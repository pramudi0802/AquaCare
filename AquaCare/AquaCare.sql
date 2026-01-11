-- Database: AqueCare

-- Create the database
CREATE DATABASE IF NOT EXISTS AqueCare;
USE AqueCare;

-- Enhanced Users Table with Admin Management
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    is_admin BOOLEAN DEFAULT FALSE,
    admin_level ENUM('none', 'moderator', 'super_admin') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    account_status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    verification_token VARCHAR(64),
    is_verified BOOLEAN DEFAULT FALSE,
    last_password_change TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    last_failed_login TIMESTAMP NULL,
    profile_image VARCHAR(255),
    INDEX idx_admin_status (is_admin, account_status),
    INDEX idx_email_verification (email, is_verified)
);

-- Admin Audit Log Table
CREATE TABLE AdminActions (
    action_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type ENUM('user_creation', 'user_suspension', 'user_deletion', 'user_reactivation', 'profile_update') NOT NULL,
    target_user_id INT,
    action_details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES Users(user_id),
    FOREIGN KEY (target_user_id) REFERENCES Users(user_id)
);

-- Stored Procedure for Admin to Add Users
DELIMITER //
CREATE PROCEDURE AdminAddUser(
    IN p_admin_id INT,
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_is_admin BOOLEAN,
    IN p_admin_level VARCHAR(20),
    OUT p_result_message VARCHAR(255)
BEGIN
    DECLARE admin_check BOOLEAN;
    DECLARE admin_level_check VARCHAR(20);
    
    -- Verify requesting user is an admin
    SELECT is_admin, admin_level INTO admin_check, admin_level_check 
    FROM Users WHERE user_id = p_admin_id;
    
    IF admin_check = FALSE OR admin_level_check = 'none' THEN
        SET p_result_message = 'Error: Unauthorized - Admin privileges required';
    ELSEIF EXISTS (SELECT 1 FROM Users WHERE username = p_username) THEN
        SET p_result_message = 'Error: Username already exists';
    ELSEIF EXISTS (SELECT 1 FROM Users WHERE email = p_email) THEN
        SET p_result_message = 'Error: Email already exists';
    ELSE
        -- Only super_admins can create other admins
        IF p_is_admin = TRUE AND admin_level_check != 'super_admin' THEN
            SET p_result_message = 'Error: Only super admins can create admin accounts';
        ELSE
            -- Insert the new user
            INSERT INTO Users (
                username, email, password_hash, first_name, last_name, 
                is_admin, admin_level, is_verified
            ) VALUES (
                p_username, p_email, p_password_hash, p_first_name, p_last_name,
                p_is_admin, IF(p_is_admin, p_admin_level, 'none'), TRUE
            );
            
            -- Log the admin action
            INSERT INTO AdminActions (
                admin_id, action_type, target_user_id, action_details
            ) VALUES (
                p_admin_id, 'user_creation', LAST_INSERT_ID(),
                CONCAT('Created ', IF(p_is_admin, 'admin', 'user'), ' account')
            );
            
            SET p_result_message = CONCAT('Success: ', IF(p_is_admin, 'Admin', 'User'), ' account created');
        END IF;
    END IF;
END //
DELIMITER ;

-- Stored Procedure for Admin to Manage User Status
DELIMITER //
CREATE PROCEDURE AdminManageUserStatus(
    IN p_admin_id INT,
    IN p_target_user_id INT,
    IN p_new_status ENUM('active', 'suspended', 'deleted'),
    OUT p_result_message VARCHAR(255)
BEGIN
    DECLARE admin_check BOOLEAN;
    DECLARE admin_level_check VARCHAR(20);
    DECLARE target_is_admin BOOLEAN;
    DECLARE target_admin_level VARCHAR(20);
    
    -- Verify requesting user is an admin
    SELECT is_admin, admin_level INTO admin_check, admin_level_check 
    FROM Users WHERE user_id = p_admin_id;
    
    -- Get target user info
    SELECT is_admin, admin_level INTO target_is_admin, target_admin_level
    FROM Users WHERE user_id = p_target_user_id;
    
    IF admin_check = FALSE OR admin_level_check = 'none' THEN
        SET p_result_message = 'Error: Unauthorized - Admin privileges required';
    ELSEIF p_admin_id = p_target_user_id THEN
        SET p_result_message = 'Error: Cannot modify your own account status';
    ELSEIF target_is_admin = TRUE AND admin_level_check != 'super_admin' THEN
        SET p_result_message = 'Error: Only super admins can modify other admin accounts';
    ELSEIF target_is_admin = TRUE AND target_admin_level = 'super_admin' THEN
        SET p_result_message = 'Error: Cannot modify super admin accounts';
    ELSE
        -- Update the user status
        UPDATE Users 
        SET account_status = p_new_status
        WHERE user_id = p_target_user_id;
        
        -- Log the admin action
        INSERT INTO AdminActions (
            admin_id, action_type, target_user_id, action_details
        ) VALUES (
            p_admin_id, 
            CASE 
                WHEN p_new_status = 'suspended' THEN 'user_suspension'
                WHEN p_new_status = 'deleted' THEN 'user_deletion'
                ELSE 'user_reactivation'
            END, 
            p_target_user_id,
            CONCAT('Changed status to ', p_new_status)
        );
        
        SET p_result_message = CONCAT('Success: User status changed to ', p_new_status);
    END IF;
END //
DELIMITER ;

-- Trigger to log admin privilege changes
DELIMITER //
CREATE TRIGGER log_admin_changes
AFTER UPDATE ON Users
FOR EACH ROW
BEGIN
    IF NEW.is_admin != OLD.is_admin OR NEW.admin_level != OLD.admin_level THEN
        INSERT INTO AdminActions (
            admin_id, action_type, target_user_id, action_details
        ) VALUES (
            NEW.user_id, 'profile_update', NEW.user_id,
            CONCAT('Admin privileges changed: ',
                   'is_admin=', IF(NEW.is_admin, 'true', 'false'),
                   ', admin_level=', NEW.admin_level)
        );
    END IF;
END //
DELIMITER ;

-- Trigger to prevent self-demotion
DELIMITER //
CREATE TRIGGER prevent_self_demotion
BEFORE UPDATE ON Users
FOR EACH ROW
BEGIN
    IF NEW.user_id = OLD.user_id AND 
       (NEW.is_admin = FALSE OR NEW.admin_level = 'none') AND 
       OLD.admin_level = 'super_admin' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Super admins cannot demote themselves';
    END IF;
END //
DELIMITER ;

-- Fish Table
CREATE TABLE Fish (
    fish_id INT AUTO_INCREMENT PRIMARY KEY,
    common_name VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(100),
    family VARCHAR(100),
    origin VARCHAR(100),
    adult_size VARCHAR(50),
    lifespan VARCHAR(50),
    min_tank_size VARCHAR(50),
    temp_range VARCHAR(50),
    ph_range VARCHAR(50),
    temperament VARCHAR(50),
    care_level VARCHAR(50),
    diet VARCHAR(50),
    description TEXT,
    water_type ENUM('Freshwater', 'Saltwater', 'Brackish') NOT NULL,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES Users(user_id)
);

-- Food Types Table
CREATE TABLE FoodTypes (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('live', 'frozen', 'commercial') NOT NULL,
    description TEXT,
    suitable_for TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Compatibility Table
CREATE TABLE Compatibility (
    compatibility_id INT AUTO_INCREMENT PRIMARY KEY,
    fish1_id INT NOT NULL,
    fish2_id INT NOT NULL,
    compatibility_status ENUM('compatible', 'incompatible', 'caution') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fish1_id) REFERENCES Fish(fish_id),
    FOREIGN KEY (fish2_id) REFERENCES Fish(fish_id),
    CHECK (fish1_id != fish2_id),
    UNIQUE KEY unique_pair (fish1_id, fish2_id)
);

-- Audit Log Table
CREATE TABLE AuditLog (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values TEXT,
    new_values TEXT,
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (changed_by) REFERENCES Users(user_id)
);

-- Disease Table
CREATE TABLE Diseases (
    disease_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    common_in TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Symptoms Table
CREATE TABLE Symptoms (
    symptom_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Disease Symptoms Junction Table
CREATE TABLE DiseaseSymptoms (
    disease_id INT NOT NULL,
    symptom_id INT NOT NULL,
    PRIMARY KEY (disease_id, symptom_id),
    FOREIGN KEY (disease_id) REFERENCES Diseases(disease_id),
    FOREIGN KEY (symptom_id) REFERENCES Symptoms(symptom_id)
);

-- Treatments Table
CREATE TABLE Treatments (
    treatment_id INT AUTO_INCREMENT PRIMARY KEY,
    disease_id INT NOT NULL,
    description TEXT NOT NULL,
    effectiveness ENUM('high', 'medium', 'low') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disease_id) REFERENCES Diseases(disease_id)
);

-- Fish Diseases Junction Table
CREATE TABLE FishDiseases (
    fish_id INT NOT NULL,
    disease_id INT NOT NULL,
    prevalence ENUM('common', 'uncommon', 'rare') DEFAULT 'common',
    PRIMARY KEY (fish_id, disease_id),
    FOREIGN KEY (fish_id) REFERENCES Fish(fish_id),
    FOREIGN KEY (disease_id) REFERENCES Diseases(disease_id)
);

-- Feeding Guides Table
CREATE TABLE FeedingGuides (
    guide_id INT AUTO_INCREMENT PRIMARY KEY,
    fish_id INT NOT NULL,
    diet_type ENUM('herbivore', 'carnivore', 'omnivore') NOT NULL,
    feeding_frequency VARCHAR(100) NOT NULL,
    live_food TEXT,
    frozen_food TEXT,
    commercial_food TEXT,
    special_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fish_id) REFERENCES Fish(fish_id)
);

-- Password Resets Table
CREATE TABLE PasswordResets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (token),
    INDEX (email)
);

-- Stored Procedures

-- Add Fish with Audit Logging
DELIMITER //
CREATE PROCEDURE AddFishWithLogging(
    IN p_common_name VARCHAR(100),
    IN p_scientific_name VARCHAR(100),
    IN p_family VARCHAR(100),
    IN p_origin VARCHAR(100),
    IN p_adult_size VARCHAR(50),
    IN p_lifespan VARCHAR(50),
    IN p_min_tank_size VARCHAR(50),
    IN p_temp_range VARCHAR(50),
    IN p_ph_range VARCHAR(50),
    IN p_temperament VARCHAR(50),
    IN p_care_level VARCHAR(50),
    IN p_diet VARCHAR(50),
    IN p_description TEXT,
    IN p_water_type ENUM('Freshwater', 'Saltwater', 'Brackish'),
    IN p_image_path VARCHAR(255),
    IN p_user_id INT
)
BEGIN
    DECLARE new_fish_id INT;
    
    INSERT INTO Fish (
        common_name, scientific_name, family, origin, adult_size, lifespan,
        min_tank_size, temp_range, ph_range, temperament, care_level,
        diet, description, water_type, image_path, created_by
    ) VALUES (
        p_common_name, p_scientific_name, p_family, p_origin, p_adult_size, p_lifespan,
        p_min_tank_size, p_temp_range, p_ph_range, p_temperament, p_care_level,
        p_diet, p_description, p_water_type, p_image_path, p_user_id
    );
    
    SET new_fish_id = LAST_INSERT_ID();
    
    INSERT INTO AuditLog (table_name, record_id, action, old_values, new_values, changed_by)
    VALUES ('Fish', new_fish_id, 'INSERT', NULL, 
            CONCAT('Added fish: ', p_common_name), p_user_id);
END //
DELIMITER ;

-- Check Compatibility
DELIMITER //
CREATE PROCEDURE CheckCompatibility(
    IN p_fish1_id INT,
    IN p_fish2_id INT,
    OUT p_compatibility_status VARCHAR(20),
    OUT p_notes TEXT
)
BEGIN
    DECLARE fish1_water_type VARCHAR(20);
    DECLARE fish2_water_type VARCHAR(20);
    DECLARE fish1_temperament VARCHAR(50);
    DECLARE fish2_temperament VARCHAR(50);
    
    SELECT water_type INTO fish1_water_type FROM Fish WHERE fish_id = p_fish1_id;
    SELECT water_type INTO fish2_water_type FROM Fish WHERE fish_id = p_fish2_id;
    
    IF fish1_water_type != fish2_water_type THEN
        SET p_compatibility_status = 'incompatible';
        SET p_notes = CONCAT('Different water types: ', fish1_water_type, ' vs ', fish2_water_type);
    ELSE
        SELECT temperament INTO fish1_temperament FROM Fish WHERE fish_id = p_fish1_id;
        SELECT temperament INTO fish2_temperament FROM Fish WHERE fish_id = p_fish2_id;
        
        IF EXISTS (
            SELECT 1 FROM Compatibility 
            WHERE (fish1_id = p_fish1_id AND fish2_id = p_fish2_id)
            OR (fish1_id = p_fish2_id AND fish2_id = p_fish1_id)
        ) THEN
            SELECT compatibility_status, notes INTO p_compatibility_status, p_notes
            FROM Compatibility
            WHERE (fish1_id = p_fish1_id AND fish2_id = p_fish2_id)
            OR (fish1_id = p_fish2_id AND fish2_id = p_fish1_id)
            LIMIT 1;
        ELSE
            IF fish1_temperament = 'Aggressive' OR fish2_temperament = 'Aggressive' THEN
                SET p_compatibility_status = 'incompatible';
                SET p_notes = 'One or both fish are aggressive';
            ELSEIF fish1_temperament = 'Semi-aggressive' OR fish2_temperament = 'Semi-aggressive' THEN
                SET p_compatibility_status = 'caution';
                SET p_notes = 'One or both fish are semi-aggressive - monitor carefully';
            ELSE
                SET p_compatibility_status = 'compatible';
                SET p_notes = 'Both fish have peaceful temperaments';
            END IF;
        END IF;
    END IF;
END //
DELIMITER ;

-- Triggers

-- Log Fish Updates
DELIMITER //
CREATE TRIGGER log_fish_update
AFTER UPDATE ON Fish
FOR EACH ROW
BEGIN
    DECLARE changes TEXT DEFAULT '';
    
    IF NEW.common_name != OLD.common_name THEN
        SET changes = CONCAT(changes, 'common_name: ', OLD.common_name, ' -> ', NEW.common_name, '; ');
    END IF;
    
    IF NEW.water_type != OLD.water_type THEN
        SET changes = CONCAT(changes, 'water_type: ', OLD.water_type, ' -> ', NEW.water_type, '; ');
    END IF;
    
    IF NEW.temperament != OLD.temperament THEN
        SET changes = CONCAT(changes, 'temperament: ', OLD.temperament, ' -> ', NEW.temperament, '; ');
    END IF;
    
    IF changes != '' THEN
        INSERT INTO AuditLog (table_name, record_id, action, old_values, new_values, changed_by)
        VALUES ('Fish', NEW.fish_id, 'UPDATE', changes, 'Fish details updated', NEW.created_by);
    END IF;
END //
DELIMITER ;

-- Prevent Fish Deletion if Referenced
DELIMITER //
CREATE TRIGGER prevent_fish_deletion
BEFORE DELETE ON Fish
FOR EACH ROW
BEGIN
    DECLARE ref_count INT;
    
    SELECT COUNT(*) INTO ref_count FROM Compatibility 
    WHERE fish1_id = OLD.fish_id OR fish2_id = OLD.fish_id;
    
    IF ref_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete fish referenced in compatibility records';
    END IF;
END //
DELIMITER ;

-- Log Food Type Changes
DELIMITER //
CREATE TRIGGER log_food_changes
AFTER INSERT ON FoodTypes
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (table_name, record_id, action, old_values, new_values, changed_by)
    VALUES ('FoodTypes', NEW.food_id, 'INSERT', NULL, CONCAT('Added food: ', NEW.name), 1);
END //
DELIMITER ;

-- Sample Data Insertion

-- Admin User
INSERT INTO Users (username, email, password_hash, first_name, last_name, is_admin, is_active)
VALUES ('admin', 'admin@aquecare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', TRUE, TRUE);

-- Regular User
INSERT INTO Users (username, email, password_hash, first_name, last_name, is_active)
VALUES ('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', TRUE);

-- Fish Data
INSERT INTO Fish (common_name, scientific_name, family, origin, adult_size, lifespan, min_tank_size, temp_range, ph_range, temperament, care_level, diet, description, water_type, created_by)
VALUES 
('Goldfish', 'Carassius auratus', 'Cyprinidae', 'East Asia', '6-18 inches', '10-15 years', '20 gallons', '65-75°F', '6.0-8.0', 'Peaceful', 'Easy', 'Omnivore', 'Goldfish are one of the most common aquarium fish, known for their bright colors and hardy nature.', 'Freshwater', 1),
('Betta', 'Betta splendens', 'Osphronemidae', 'Southeast Asia', '2.5-3 inches', '3-5 years', '5 gallons', '75-80°F', '6.5-7.5', 'Aggressive', 'Easy', 'Carnivore', 'Bettas are known for their vibrant colors and long fins. Males are territorial and should be kept alone.', 'Freshwater', 1),
('Blue Tang', 'Paracanthurus hepatus', 'Acanthuridae', 'Indo-Pacific', '10-12 inches', '8-12 years', '100 gallons', '72-78°F', '8.1-8.4', 'Peaceful', 'Moderate', 'Herbivore', 'Popularized by the movie Finding Nemo, Blue Tangs are active swimmers that need plenty of space.', 'Saltwater', 1),
('Clownfish', 'Amphiprion ocellaris', 'Pomacentridae', 'Indo-Pacific', '3-4 inches', '6-10 years', '20 gallons', '75-82°F', '8.0-8.4', 'Peaceful', 'Easy', 'Omnivore', 'Clownfish form symbiotic relationships with sea anemones and are popular in marine aquariums.', 'Saltwater', 1),
('Archerfish', 'Toxotes jaculatrix', 'Toxotidae', 'Southeast Asia', '6-8 inches', '5-7 years', '30 gallons', '75-85°F', '7.0-8.5', 'Peaceful', 'Moderate', 'Carnivore', 'Archerfish are known for their ability to shoot down insects with water droplets.', 'Brackish', 1);

-- Food Types
INSERT INTO FoodTypes (name, type, description, suitable_for)
VALUES 
('Brine Shrimp', 'live', 'Nutritious live food high in protein', 'Most freshwater and saltwater fish'),
('Bloodworms', 'frozen', 'Freeze-dried or frozen larvae of midge flies', 'Bettas, goldfish, tetras, and other small fish'),
('Flake Food', 'commercial', 'Basic staple diet for most aquarium fish', 'Community freshwater fish'),
('Spirulina Flakes', 'commercial', 'Algae-based flakes rich in nutrients', 'Herbivorous fish like tangs and mollies'),
('Mysis Shrimp', 'frozen', 'Small crustaceans high in fatty acids', 'Marine fish and larger freshwater species');

-- Compatibility Data
INSERT INTO Compatibility (fish1_id, fish2_id, compatibility_status, notes)
VALUES 
(1, 2, 'incompatible', 'Bettas may attack goldfish fins'),
(1, 3, 'incompatible', 'Different water types'),
(2, 4, 'incompatible', 'Different water types'),
(3, 4, 'compatible', 'Both are peaceful saltwater species'),
(1, 5, 'caution', 'Archerfish prefer brackish water while goldfish prefer freshwater');

-- Diseases
INSERT INTO Diseases (name, description, common_in)
VALUES 
('Ich (White Spot Disease)', 'Parasitic infection causing white spots on body and fins', 'Freshwater fish, especially stressed individuals'),
('Fin Rot', 'Bacterial infection causing deterioration of fins', 'Fish with poor water conditions'),
('Velvet Disease', 'Parasitic infection causing gold/rust colored dust on skin', 'Both freshwater and saltwater fish'),
('Swim Bladder Disorder', 'Affects fish buoyancy, causing floating or sinking', 'Goldfish, bettas, and other compressed-body fish'),
('Marine Ich', 'Saltwater version of ich caused by Cryptocaryon irritans', 'Marine fish, especially new additions');

-- Symptoms
INSERT INTO Symptoms (name, description)
VALUES 
('White spots', 'Small white dots resembling salt grains on body/fins'),
('Rapid gill movement', 'Fish breathing heavily with fast gill movement'),
('Clamped fins', 'Fins held close to the body instead of spread out'),
('Flashing', 'Fish rubbing against objects in the tank'),
('Loss of appetite', 'Fish not eating or showing reduced interest in food');

-- Disease-Symptom Relationships
INSERT INTO DiseaseSymptoms (disease_id, symptom_id)
VALUES 
(1, 1), (1, 2), (1, 4),
(2, 3), (2, 5),
(3, 1), (3, 3), (3, 4),
(4, 5), (4, 3),
(5, 1), (5, 2), (5, 4);

-- Treatments
INSERT INTO Treatments (disease_id, description, effectiveness)
VALUES 
(1, 'Raise temperature to 86°F for freshwater ich (if fish can tolerate)', 'high'),
(1, 'Use copper-based medication for marine ich', 'high'),
(2, 'Improve water quality and use antibacterial medication', 'high'),
(3, 'Use copper-based medication and reduce lighting', 'medium'),
(4, 'Fast fish for 24-48 hours then feed peeled peas', 'medium'),
(5, 'Hyposalinity treatment for marine fish', 'high');

-- Fish-Disease Relationships
INSERT INTO FishDiseases (fish_id, disease_id, prevalence)
VALUES
(1, 1, 'common'), (1, 2, 'common'), (1, 4, 'common'),
(2, 1, 'common'), (2, 2, 'common'), (2, 3, 'uncommon'),
(3, 5, 'common'), (4, 5, 'common'), (5, 1, 'rare');

-- Feeding Guides
INSERT INTO FeedingGuides (fish_id, diet_type, feeding_frequency, live_food, frozen_food, commercial_food, special_notes)
VALUES
(1, 'omnivore', '2-3 times daily', 'Brine shrimp, bloodworms', 'Bloodworms, daphnia', 'High-quality flakes, sinking pellets', 'Avoid overfeeding which can cause swim bladder issues'),
(2, 'carnivore', '2 times daily', 'Brine shrimp, mosquito larvae', 'Bloodworms, mysis shrimp', 'Betta-specific pellets', 'Feed only what they can consume in 2 minutes'),
(3, 'herbivore', '3-4 times daily', NULL, 'Spirulina-enriched foods', 'Marine algae sheets, herbivore pellets', 'Requires constant grazing opportunities'),
(4, 'omnivore', '2-3 times daily', 'Brine shrimp, copepods', 'Mysis shrimp, krill', 'Marine flakes, pellets', 'Varied diet promotes coloration'),
(5, 'carnivore', '2-3 times daily', 'Crickets, mealworms (land insects)', 'Bloodworms, brine shrimp', 'Floating carnivore pellets', 'Will learn to take food from surface');


-- Enhanced FoodTypes Table with Additional Fields
CREATE TABLE FoodTypes (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('live', 'frozen', 'commercial') NOT NULL,
    description TEXT,
    nutritional_info JSON,
    suitable_for TEXT,
    storage_instructions TEXT,
    shelf_life VARCHAR(50),
    price_range VARCHAR(50),
    is_organic BOOLEAN DEFAULT FALSE,
    is_vegetarian BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES Users(user_id),
    FULLTEXT INDEX ft_search (name, description, suitable_for)
);

-- FishFoodPreferences Table (junction table)
CREATE TABLE FishFoodPreferences (
    fish_id INT NOT NULL,
    food_id INT NOT NULL,
    preference_level ENUM('preferred', 'accepted', 'occasional') DEFAULT 'accepted',
    notes TEXT,
    PRIMARY KEY (fish_id, food_id),
    FOREIGN KEY (fish_id) REFERENCES Fish(fish_id),
    FOREIGN KEY (food_id) REFERENCES FoodTypes(food_id)
);

-- Stored Procedure to Get Foods by Type
DELIMITER //
CREATE PROCEDURE GetFoodsByType(
    IN p_food_type VARCHAR(20),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SET @sql = CONCAT('
        SELECT 
            food_id, 
            name, 
            type, 
            description, 
            suitable_for,
            storage_instructions,
            price_range
        FROM FoodTypes
        WHERE type = ?
        ORDER BY name
        LIMIT ? OFFSET ?');
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt USING p_food_type, p_limit, p_offset;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

-- Stored Procedure to Add New Food Type
DELIMITER //
CREATE PROCEDURE AddFoodType(
    IN p_name VARCHAR(100),
    IN p_type ENUM('live', 'frozen', 'commercial'),
    IN p_description TEXT,
    IN p_nutritional_info JSON,
    IN p_suitable_for TEXT,
    IN p_storage_instructions TEXT,
    IN p_shelf_life VARCHAR(50),
    IN p_price_range VARCHAR(50),
    IN p_is_organic BOOLEAN,
    IN p_is_vegetarian BOOLEAN,
    IN p_user_id INT,
    OUT p_food_id INT
)
BEGIN
    INSERT INTO FoodTypes (
        name, 
        type, 
        description, 
        nutritional_info,
        suitable_for,
        storage_instructions,
        shelf_life,
        price_range,
        is_organic,
        is_vegetarian,
        created_by
    ) VALUES (
        p_name, 
        p_type, 
        p_description, 
        p_nutritional_info,
        p_suitable_for,
        p_storage_instructions,
        p_shelf_life,
        p_price_range,
        p_is_organic,
        p_is_vegetarian,
        p_user_id
    );
    
    SET p_food_id = LAST_INSERT_ID();
    
    -- Log the addition
    INSERT INTO AuditLog (table_name, record_id, action, changed_by)
    VALUES ('FoodTypes', p_food_id, 'INSERT', p_user_id);
END //
DELIMITER ;

-- Sample Data for FoodTypes
INSERT INTO FoodTypes (name, type, description, nutritional_info, suitable_for, storage_instructions, shelf_life, price_range, is_organic, is_vegetarian, created_by) VALUES
-- Live Foods
('Brine Shrimp', 'live', 'Nutritious live food high in protein', 
 '{"protein":"60%", "fat":"20%", "fiber":"5%", "moisture":"70%"}', 
 'Most freshwater and saltwater fish', 
 'Keep in aerated saltwater at 40-50°F', '7 days', '$5-$10', FALSE, FALSE, 1),

('Daphnia', 'live', 'Small freshwater crustaceans excellent for small fish', 
 '{"protein":"45%", "fat":"10%", "fiber":"15%", "moisture":"80%"}', 
 'Small tropical fish, bettas, fry', 
 'Keep in freshwater at 50-60°F with gentle aeration', '5 days', '$3-$8', FALSE, FALSE, 1),

-- Frozen Foods
('Bloodworms', 'frozen', 'Freeze-dried or frozen larvae of midge flies', 
 '{"protein":"55%", "fat":"15%", "fiber":"5%", "moisture":"10%"}', 
 'Bettas, goldfish, tetras, and other small fish', 
 'Keep frozen at -4°F or below', '1 year', '$8-$15', FALSE, FALSE, 1),

('Mysis Shrimp', 'frozen', 'Small crustaceans high in fatty acids', 
 '{"protein":"50%", "fat":"25%", "fiber":"8%", "moisture":"12%"}', 
 'Marine fish and larger freshwater species', 
 'Keep frozen at -4°F or below', '1 year', '$10-$20', FALSE, FALSE, 1),

-- Commercial Foods
('Spirulina Flakes', 'commercial', 'Algae-based flakes rich in nutrients', 
 '{"protein":"45%", "fat":"8%", "fiber":"5%", "moisture":"8%"}', 
 'Herbivorous fish like tangs and mollies', 
 'Store in cool, dry place', '2 years', '$5-$12', TRUE, TRUE, 1),

('Premium Cichlid Pellets', 'commercial', 'Balanced diet for cichlids', 
 '{"protein":"40%", "fat":"12%", "fiber":"3%", "moisture":"10%"}', 
 'African and South American cichlids', 
 'Store in airtight container in cool place', '18 months', '$15-$30', FALSE, FALSE, 1);

-- Sample Fish Food Preferences
INSERT INTO FishFoodPreferences (fish_id, food_id, preference_level, notes) VALUES
-- Goldfish preferences
(1, 1, 'preferred', 'Excellent for coloration'),
(1, 3, 'accepted', 'Good protein source'),
(1, 5, 'occasional', 'For variety in diet'),

-- Betta preferences
(2, 1, 'preferred', 'Small size perfect for bettas'),
(2, 3, 'preferred', 'Main staple food'),
(2, 6, 'accepted', 'Only small pellets');

-- Trigger to validate food type on insert
DELIMITER //
CREATE TRIGGER validate_food_type
BEFORE INSERT ON FoodTypes
FOR EACH ROW
BEGIN
    IF NEW.type NOT IN ('live', 'frozen', 'commercial') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid food type. Must be live, frozen, or commercial';
    END IF;
END //
DELIMITER ;

-- Trigger to update timestamp on food type modification
DELIMITER //
CREATE TRIGGER update_food_timestamp
BEFORE UPDATE ON FoodTypes
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //
DELIMITER ;