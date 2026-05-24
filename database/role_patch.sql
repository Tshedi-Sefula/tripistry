USE tripistry;

-- -------------------------------------------------------
-- 1. Create the user supertype table
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS User (
    userID       INT          NOT NULL AUTO_INCREMENT,
    email        VARCHAR(150) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    role         ENUM('traveller','agency') NOT NULL,
    createdAt    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- 2. Migrate existing traveller auth data into user
-- -------------------------------------------------------
INSERT INTO User (email, passwordHash, role)
SELECT email, passwordHash, 'traveller'
FROM Traveller;

-- Add userID FK column to traveller
ALTER TABLE Traveller ADD COLUMN userID INT UNIQUE AFTER travellerID;

-- Link each traveller row to its new user row
UPDATE Traveller t
JOIN User u ON u.email = t.email AND u.role = 'traveller'
SET t.userID = u.userID;

-- Now drop the redundant auth columns from traveller
ALTER TABLE Traveller
    DROP COLUMN email,
    DROP COLUMN passwordHash,
    ADD CONSTRAINT fk_traveller_user
        FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE;

-- -------------------------------------------------------
-- 3. Migrate existing travelAgency auth data into user
-- -------------------------------------------------------
INSERT INTO User (email, passwordHash, role)
SELECT email, passwordHash, 'agency'
FROM TravelAgency;

-- Add userID FK column to travelAgency
ALTER TABLE TravelAgency ADD COLUMN userID INT UNIQUE AFTER agencyID;

-- Link each agency row to its new user row
UPDATE TravelAgency a
JOIN User u ON u.email = a.email AND u.role = 'agency'
SET a.userID = u.userID;

-- Drop redundant auth columns from travelAgency
ALTER TABLE TravelAgency
    DROP COLUMN email,
    DROP COLUMN passwordHash,
    ADD CONSTRAINT fk_agency_user
        FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE;

-- -------------------------------------------------------
-- 4. Trigger: prevent a userID being used in both subtypes
-- -------------------------------------------------------
DELIMITER $$

CREATE OR REPLACE TRIGGER trg_traveller_role_check
BEFORE INSERT ON Traveller
FOR EACH ROW
BEGIN
    DECLARE v_role ENUM('traveller','agency');
    SELECT role INTO v_role FROM User WHERE userID = NEW.userID;
    IF v_role != 'traveller' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User role must be traveller to insert into traveller table.';
    END IF;
END$$

CREATE OR REPLACE TRIGGER trg_agency_role_check
BEFORE INSERT ON TravelAgency
FOR EACH ROW
BEGIN
    DECLARE v_role ENUM('traveller','agency');
    SELECT role INTO v_role FROM User WHERE userID = NEW.userID;
    IF v_role != 'agency' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User role must be agency to insert into travelAgency table.';
    END IF;
END$$

DELIMITER ;
