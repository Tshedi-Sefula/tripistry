-- ============================================================
--  Tripistry - Task 6: Data Population Script
--  Targets: MariaDB
--  Generated: 2026-05-13
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
--  1. SCHEMA CREATION
-- ============================================================

DROP DATABASE IF EXISTS tripistry;
CREATE DATABASE tripistry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tripistry;

-- -------------------------------------------------------
-- traveller
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS traveller (
    travellerID  INT            NOT NULL AUTO_INCREMENT,
    firstName    VARCHAR(60)    NOT NULL,
    lastName     VARCHAR(60)    NOT NULL,
    name         VARCHAR(120)   GENERATED ALWAYS AS (CONCAT(firstName,' ',lastName)) STORED,
    email        VARCHAR(150)   NOT NULL UNIQUE,
    passwordHash VARCHAR(255)   NOT NULL,
    phoneNo      VARCHAR(25),
    nationality  VARCHAR(80),
    dateOfBirth  DATE,
    joinDate     DATE           NOT NULL DEFAULT (CURDATE()),
    preferences  VARCHAR(500),
    PRIMARY KEY (travellerID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- travelAgency
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS travelAgency (
    agencyID         INT           NOT NULL AUTO_INCREMENT,
    name             VARCHAR(120)  NOT NULL,
    email            VARCHAR(150)  NOT NULL UNIQUE,
    passwordHash     VARCHAR(255)  NOT NULL,
    phone            VARCHAR(25),
    website          VARCHAR(200),
    address          VARCHAR(300),
    registrationDate DATE          NOT NULL DEFAULT (CURDATE()),
    rating           DECIMAL(3,2)  DEFAULT 0.00  CHECK (rating BETWEEN 0 AND 5),
    description      TEXT,
    PRIMARY KEY (agencyID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- destination
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS destination (
    destinationID  INT            NOT NULL AUTO_INCREMENT,
    name           VARCHAR(120)   NOT NULL,
    country        VARCHAR(80)    NOT NULL,
    description    TEXT,
    latitude       DECIMAL(9,6),
    longitude      DECIMAL(9,6),
    popularityScore INT           DEFAULT 0,
    PRIMARY KEY (destinationID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- flight
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS flight (
    flightID            INT           NOT NULL AUTO_INCREMENT,
    airline             VARCHAR(100)  NOT NULL,
    departureAirport    VARCHAR(100)  NOT NULL,
    arrivalAirport      VARCHAR(100)  NOT NULL,
    departureDatetime   DATETIME      NOT NULL,
    arrivalDatetime     DATETIME      NOT NULL,
    price               DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    seatsAvailable      INT           NOT NULL DEFAULT 0 CHECK (seatsAvailable >= 0),
    PRIMARY KEY (flightID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- restaurant
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant (
    restaurantID   INT           NOT NULL AUTO_INCREMENT,
    name           VARCHAR(120)  NOT NULL,
    cuisineType    VARCHAR(80),
    address        VARCHAR(300),
    rating         DECIMAL(3,2)  DEFAULT 0.00 CHECK (rating BETWEEN 0 AND 5),
    priceRange     VARCHAR(10)   CHECK (priceRange IN ('$','$$','$$$','$$$$')),
    destinationID  INT           NOT NULL,
    PRIMARY KEY (restaurantID),
    FOREIGN KEY (destinationID) REFERENCES destination(destinationID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- accomodation  (matches diagram spelling)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS accomodation (
    accomodationID  INT           NOT NULL AUTO_INCREMENT,
    name            VARCHAR(120)  NOT NULL,
    type            VARCHAR(60),
    address         VARCHAR(300),
    rating          DECIMAL(3,2)  DEFAULT 0.00 CHECK (rating BETWEEN 0 AND 5),
    pricePerNight   DECIMAL(10,2) NOT NULL CHECK (pricePerNight >= 0),
    ammenities      TEXT,
    PRIMARY KEY (accomodationID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- touristAttraction
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS touristAttraction (
    attractionID   INT           NOT NULL AUTO_INCREMENT,
    name           VARCHAR(120)  NOT NULL,
    category       VARCHAR(80),
    description    TEXT,
    ticketPrice    DECIMAL(10,2) DEFAULT 0.00 CHECK (ticketPrice >= 0),
    PRIMARY KEY (attractionID)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- travelPackage  (supertype; specialised into RegularPackage & GroupTrip)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS travelPackage (
    packageID        INT            NOT NULL AUTO_INCREMENT,
    agencyID         INT            NOT NULL,
    title            VARCHAR(200)   NOT NULL,
    description      TEXT,
    basePrice        DECIMAL(10,2)  NOT NULL CHECK (basePrice >= 0),
    totalPrice       DECIMAL(10,2)  GENERATED ALWAYS AS (basePrice) STORED, -- extended by subtype logic
    durationDays     INT            NOT NULL CHECK (durationDays > 0),
    startDate        DATE,
    endDate          DATE,
    itinerary        TEXT,
    status           ENUM('active','inactive','draft') DEFAULT 'active',
    dateCreated      DATE           NOT NULL DEFAULT (CURDATE()),
    packageType      ENUM('regular','group') NOT NULL DEFAULT 'regular',
    PRIMARY KEY (packageID),
    FOREIGN KEY (agencyID) REFERENCES travelAgency(agencyID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- RegularPackage  (specialisation)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS RegularPackage (
    packageID   INT  NOT NULL,
    PRIMARY KEY (packageID),
    FOREIGN KEY (packageID) REFERENCES travelPackage(packageID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- GroupTrip  (specialisation + entity in diagram)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS GroupTrip (
    packageID        INT           NOT NULL,
    minGroupSize     INT           NOT NULL DEFAULT 2,
    maxGroupSize     INT           NOT NULL,
    currentGroupSize INT           NOT NULL DEFAULT 0,
    groupDeadline    DATE,
    groupDescription TEXT,
    PRIMARY KEY (packageID),
    FOREIGN KEY (packageID) REFERENCES travelPackage(packageID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- booking
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS booking (
    bookingID      INT            NOT NULL AUTO_INCREMENT,
    travellerID    INT            NOT NULL,
    packageID      INT            NOT NULL,
    bookingDate    DATE           NOT NULL DEFAULT (CURDATE()),
    numTravellers  INT            NOT NULL DEFAULT 1 CHECK (numTravellers >= 1),
    totalAmount    DECIMAL(12,2)  NOT NULL CHECK (totalAmount >= 0),
    status         ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    paymentStatus  ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    PRIMARY KEY (bookingID),
    FOREIGN KEY (travellerID) REFERENCES traveller(travellerID) ON DELETE RESTRICT,
    FOREIGN KEY (packageID)   REFERENCES travelPackage(packageID) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- review  (disjoint specialisation: targets package OR agency)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS review (
    reviewID    INT           NOT NULL AUTO_INCREMENT,
    travellerID INT           NOT NULL,
    targetType  ENUM('package','agency') NOT NULL,
    packageID   INT,
    agencyID    INT,
    rating      DECIMAL(3,2)  NOT NULL CHECK (rating BETWEEN 0 AND 5),
    comment     TEXT,
    reviewDate  DATE          NOT NULL DEFAULT (CURDATE()),
    PRIMARY KEY (reviewID),
    FOREIGN KEY (travellerID) REFERENCES traveller(travellerID) ON DELETE CASCADE,
    FOREIGN KEY (packageID)   REFERENCES travelPackage(packageID) ON DELETE SET NULL,
    FOREIGN KEY (agencyID)    REFERENCES travelAgency(agencyID)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- Enforce disjoint review target via trigger (MariaDB CHECK can't reference nullable FK columns)
DELIMITER $$
CREATE OR REPLACE TRIGGER trg_review_target_check
BEFORE INSERT ON review
FOR EACH ROW
BEGIN
    IF NOT (
        (NEW.targetType = 'package' AND NEW.packageID IS NOT NULL AND NEW.agencyID IS NULL) OR
        (NEW.targetType = 'agency'  AND NEW.agencyID  IS NOT NULL AND NEW.packageID IS NULL)
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Review must target either a package or an agency, not both or neither.';
    END IF;
END$$
DELIMITER ;

-- -------------------------------------------------------
-- Junction / relationship tables
-- -------------------------------------------------------

-- PackageDestination  (M:N travelPackage <-> destination)
CREATE TABLE IF NOT EXISTS PackageDestination (
    packageID     INT NOT NULL,
    destinationID INT NOT NULL,
    PRIMARY KEY (packageID, destinationID),
    FOREIGN KEY (packageID)     REFERENCES travelPackage(packageID)  ON DELETE CASCADE,
    FOREIGN KEY (destinationID) REFERENCES destination(destinationID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PackageFlight  (M:N travelPackage <-> flight)
CREATE TABLE IF NOT EXISTS PackageFlight (
    packageID INT NOT NULL,
    flightID  INT NOT NULL,
    PRIMARY KEY (packageID, flightID),
    FOREIGN KEY (packageID) REFERENCES travelPackage(packageID) ON DELETE CASCADE,
    FOREIGN KEY (flightID)  REFERENCES flight(flightID)         ON DELETE CASCADE
) ENGINE=InnoDB;

-- PackageAccommodation  (M:N travelPackage <-> accomodation)
CREATE TABLE IF NOT EXISTS PackageAccommodation (
    packageID      INT NOT NULL,
    accomodationID INT NOT NULL,
    staysAt        INT DEFAULT 1 CHECK (staysAt >= 1),  -- number of nights
    PRIMARY KEY (packageID, accomodationID),
    FOREIGN KEY (packageID)      REFERENCES travelPackage(packageID) ON DELETE CASCADE,
    FOREIGN KEY (accomodationID) REFERENCES accomodation(accomodationID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PackageAttraction  (M:N travelPackage <-> touristAttraction)
CREATE TABLE IF NOT EXISTS PackageAttraction (
    packageID    INT NOT NULL,
    attractionID INT NOT NULL,
    PRIMARY KEY (packageID, attractionID),
    FOREIGN KEY (packageID)    REFERENCES travelPackage(packageID)   ON DELETE CASCADE,
    FOREIGN KEY (attractionID) REFERENCES touristAttraction(attractionID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- destination visits attraction  (M:N)
CREATE TABLE IF NOT EXISTS DestinationAttraction (
    destinationID INT NOT NULL,
    attractionID  INT NOT NULL,
    PRIMARY KEY (destinationID, attractionID),
    FOREIGN KEY (destinationID) REFERENCES destination(destinationID)   ON DELETE CASCADE,
    FOREIGN KEY (attractionID)  REFERENCES touristAttraction(attractionID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- traveller unites (self-referencing review relationship already modelled)
-- traveller joins GroupTrip  (M:N)
CREATE TABLE IF NOT EXISTS TravellerGroupTrip (
    travellerID INT NOT NULL,
    packageID   INT NOT NULL,
    joinedDate  DATE NOT NULL DEFAULT (CURDATE()),
    PRIMARY KEY (travellerID, packageID),
    FOREIGN KEY (travellerID) REFERENCES traveller(travellerID)    ON DELETE CASCADE,
    FOREIGN KEY (packageID)   REFERENCES GroupTrip(packageID)      ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
--  2. DATA POPULATION
-- ============================================================

-- -------------------------------------------------------
-- travelAgency  (10 agencies)
-- -------------------------------------------------------
INSERT INTO travelAgency (name, email, passwordHash, phone, website, address, registrationDate, rating, description) VALUES
('SunWay Travels',     'info@sunway.com',       '$2y$12$abc1hashedpassword001', '+27 11 234 5678', 'https://sunway.com',       '12 Rosebank Ave, Johannesburg, ZA', '2020-03-15', 4.50, 'Premium South African travel specialist offering luxury safari and beach packages.'),
('Globe Trekkers',     'hello@globetrekkers.io','$2y$12$abc2hashedpassword002', '+44 20 7946 0958','https://globetrekkers.io',  '88 Oxford St, London, UK',           '2019-07-22', 4.20, 'Budget-friendly round-the-world itineraries for the adventurous traveller.'),
('Azure Escapes',      'book@azureescapes.com', '$2y$12$abc3hashedpassword003', '+34 91 555 0123', 'https://azureescapes.com',  'Calle Gran Via 45, Madrid, ES',      '2021-01-10', 4.70, 'Mediterranean and European holiday packages with boutique hotel stays.'),
('Horizon Adventures', 'trips@horizonadv.co',   '$2y$12$abc4hashedpassword004', '+1 212 555 0199', 'https://horizonadv.co',    '500 5th Ave, New York, US',          '2018-11-05', 3.90, 'Outdoor adventure packages including trekking, camping and extreme sports.'),
('Pearl Voyages',      'contact@pearlvoyages.com','$2y$12$abc5hashedpassword005','+61 2 9999 1234','https://pearlvoyages.com',  '22 George St, Sydney, AU',           '2022-04-18', 4.60, 'Asia-Pacific cruise and island-hopping specialists.'),
('Velvet Journeys',    'vip@velvetjourneys.com','$2y$12$abc6hashedpassword006', '+33 1 42 86 00 00','https://velvetjourneys.com','15 Rue de la Paix, Paris, FR',       '2017-09-30', 4.80, 'Luxury European river cruises and chateau tours.'),
('Terra Nomads',       'hi@terranomads.org',    '$2y$12$abc7hashedpassword007', '+49 30 12345678', 'https://terranomads.org',  'Unter den Linden 10, Berlin, DE',   '2020-06-01', 4.10, 'Eco-tourism and sustainable travel experiences worldwide.'),
('Coral Coast Tours',  'tours@coralcoast.com',  '$2y$12$abc8hashedpassword008', '+66 2 123 4567',  'https://coralcoast.com',   '99 Sukhumvit Rd, Bangkok, TH',      '2021-08-14', 4.30, 'Southeast Asia beach and island packages at every budget.'),
('Alpine Quest',       'ski@alpinequest.ch',    '$2y$12$abc9hashedpassword009', '+41 44 123 4567', 'https://alpinequest.ch',   'Bahnhofstrasse 5, Zurich, CH',      '2016-12-20', 4.55, 'Winter skiing and summer hiking packages across the Alps.'),
('Savanna Trails',     'safari@savannatrails.co.za','$2y$12$abc10hashedpassword0','+27 12 345 6789','https://savannatrails.co.za','8 Lynnwood Rd, Pretoria, ZA',    '2019-02-28', 4.65, 'African wildlife safari specialists covering the Big Five routes.');

-- -------------------------------------------------------
-- traveller  (20 travellers)
-- -------------------------------------------------------
INSERT INTO traveller (firstName, lastName, email, passwordHash, phoneNo, nationality, dateOfBirth, joinDate, preferences) VALUES
('Liam',     'Johnson',    'liam.johnson@email.com',    '$2y$12$t1hashedpw', '+27 82 111 2222', 'South African', '1990-05-14', '2024-01-10', 'beach,wildlife'),
('Amara',    'Nkosi',      'amara.nkosi@email.com',     '$2y$12$t2hashedpw', '+27 83 222 3333', 'South African', '1995-08-22', '2024-02-15', 'adventure,hiking'),
('Emma',     'Thompson',   'emma.thompson@email.com',   '$2y$12$t3hashedpw', '+44 77 333 4444', 'British',       '1988-11-30', '2024-03-01', 'culture,food'),
('Carlos',   'Rivera',     'carlos.rivera@email.com',   '$2y$12$t4hashedpw', '+52 55 444 5555', 'Mexican',       '1993-02-17', '2024-03-20', 'history,architecture'),
('Yuki',     'Tanaka',     'yuki.tanaka@email.com',     '$2y$12$t5hashedpw', '+81 90 555 6666', 'Japanese',      '1997-06-05', '2024-04-05', 'anime,food,shopping'),
('Sofia',    'Mendes',     'sofia.mendes@email.com',    '$2y$12$t6hashedpw', '+55 11 666 7777', 'Brazilian',     '1991-09-19', '2024-04-18', 'music,beach,nightlife'),
('Ahmed',    'Hassan',     'ahmed.hassan@email.com',    '$2y$12$t7hashedpw', '+20 10 777 8888', 'Egyptian',      '1986-12-01', '2024-05-02', 'history,deserts'),
('Mei',      'Chen',       'mei.chen@email.com',        '$2y$12$t8hashedpw', '+86 138 888 9999', 'Chinese',      '1999-03-25', '2024-05-15', 'shopping,food,luxury'),
('Oliver',   'Smith',      'oliver.smith@email.com',    '$2y$12$t9hashedpw', '+61 4 999 0000',  'Australian',    '1985-07-13', '2024-06-01', 'surfing,outdoor'),
('Nina',     'Petrov',     'nina.petrov@email.com',     '$2y$12$t10hashedpw','+7 916 000 1111', 'Russian',       '1994-10-28', '2024-06-20', 'ballet,museums,art'),
('Kofi',     'Asante',     'kofi.asante@email.com',     '$2y$12$t11hashedpw','+233 24 111 2222','Ghanaian',      '1992-04-03', '2024-07-10', 'culture,music'),
('Priya',    'Sharma',     'priya.sharma@email.com',    '$2y$12$t12hashedpw','+91 98 222 3333', 'Indian',        '1996-01-16', '2024-07-25', 'yoga,temples,spice'),
('Lucas',    'Dubois',     'lucas.dubois@email.com',    '$2y$12$t13hashedpw','+33 6 333 4444',  'French',        '1989-06-07', '2024-08-05', 'wine,cuisine,cycling'),
('Isla',     'MacLeod',    'isla.macleod@email.com',    '$2y$12$t14hashedpw','+44 79 444 5555', 'Scottish',      '2000-09-14', '2024-08-20', 'whisky,highlands,hiking'),
('Diego',    'Fernandez',  'diego.fernandez@email.com', '$2y$12$t15hashedpw','+34 62 555 6666', 'Spanish',       '1987-03-30', '2024-09-01', 'football,beaches,fiesta'),
('Hana',     'Yamamoto',   'hana.yamamoto@email.com',   '$2y$12$t16hashedpw','+81 80 666 7777', 'Japanese',      '1998-12-12', '2024-09-18', 'onsen,tea,gardens'),
('Ethan',    'Brown',      'ethan.brown@email.com',     '$2y$12$t17hashedpw','+1 310 777 8888', 'American',      '1993-07-22', '2024-10-01', 'hiking,national-parks'),
('Zara',     'Ali',        'zara.ali@email.com',        '$2y$12$t18hashedpw','+92 300 888 9999','Pakistani',     '2001-05-08', '2024-10-15', 'shopping,food,history'),
('Marco',    'Rossi',      'marco.rossi@email.com',     '$2y$12$t19hashedpw','+39 333 000 1111','Italian',       '1984-02-20', '2024-11-01', 'art,opera,pasta'),
('Fatima',   'Ouedraogo',  'fatima.o@email.com',        '$2y$12$t20hashedpw','+226 70 111 2222','Burkinabe',     '1997-08-11', '2024-11-20', 'culture,crafts,nature');

-- -------------------------------------------------------
-- destination  (15 destinations)
-- -------------------------------------------------------
INSERT INTO destination (name, country, description, latitude, longitude, popularityScore) VALUES
('Cape Town',        'South Africa', 'Mother City with Table Mountain, beaches and vibrant food scene.',             -33.9249,   18.4241,  95),
('Paris',            'France',       'City of Light renowned for the Eiffel Tower, cuisine and fashion.',            48.8566,    2.3522,   98),
('Bali',             'Indonesia',    'Tropical island paradise with rice terraces, temples and surf beaches.',        -8.3405,   115.0920,  93),
('New York City',    'USA',          'The Big Apple - world-class museums, Broadway and iconic skyline.',             40.7128,  -74.0060,   96),
('Tokyo',            'Japan',        'Ultramodern metropolis blending ancient traditions with cutting-edge tech.',    35.6762,  139.6503,   97),
('Santorini',        'Greece',       'Iconic white-and-blue island with caldera views and volcanic beaches.',         36.3932,   25.4615,   91),
('Machu Picchu',     'Peru',         'Incan citadel set high in the Andes Mountains above the Sacred Valley.',      -13.1631,  -72.5450,   89),
('Safari - Kruger',  'South Africa', 'World-famous national park home to the Big Five wildlife.',                   -23.9884,   31.5547,   88),
('Maldives',         'Maldives',     'Indian Ocean archipelago of coral atolls with overwater bungalows.',             4.1755,   73.5093,   94),
('Rome',             'Italy',        'Eternal City with the Colosseum, Vatican and incredible piazzas.',             41.9028,   12.4964,   96),
('Sydney',           'Australia',    'Harbour city famed for its Opera House, Bondi Beach and cosmopolitan culture.',  -33.8688, 151.2093,  93),
('Marrakech',        'Morocco',      'Imperial city with vibrant souks, riads and the famous Djemaa el-Fna.',        31.6295,   -7.9811,   87),
('Zurich',           'Switzerland',  'Alpine sophistication with pristine lakes, world-class skiing and luxury.',    47.3769,    8.5417,   85),
('Bangkok',          'Thailand',     'City of Angels bursting with street food, temples and lively nightlife.',      13.7563,  100.5018,   92),
('Zanzibar',         'Tanzania',     'Spice island with turquoise waters, coral reefs and Swahili culture.',          -6.1659,   39.2026,   84);

-- -------------------------------------------------------
-- flight  (20 flights)
-- -------------------------------------------------------
INSERT INTO flight (airline, departureAirport, arrivalAirport, departureDatetime, arrivalDatetime, price, seatsAvailable) VALUES
('South African Airways', 'OR Tambo International (JNB)',         'Cape Town International (CPT)',         '2026-07-01 06:00:00', '2026-07-01 08:10:00',  980.00, 42),
('Air France',            'Charles de Gaulle (CDG)',               'Cape Town International (CPT)',         '2026-07-05 10:30:00', '2026-07-05 23:45:00', 8500.00, 18),
('Garuda Indonesia',      'Ngurah Rai International (DPS)',        'Soekarno-Hatta International (CGK)',    '2026-07-10 07:00:00', '2026-07-10 09:30:00',  320.00, 85),
('American Airlines',     'JFK International (JFK)',               'Los Angeles International (LAX)',       '2026-07-12 08:00:00', '2026-07-12 11:30:00',  350.00, 60),
('Japan Airlines',        'Narita International (NRT)',             'Singapore Changi (SIN)',                '2026-07-14 13:00:00', '2026-07-14 19:00:00', 1200.00, 30),
('Aegean Airlines',       'Athens International (ATH)',            'Santorini National (JTR)',              '2026-07-20 09:00:00', '2026-07-20 10:05:00',  280.00, 50),
('LATAM Airlines',        'Jorge Chavez International (LIM)',      'Alejandro Velasco (CUZ)',               '2026-08-01 06:30:00', '2026-08-01 07:50:00',  420.00, 40),
('South African Airways', 'OR Tambo International (JNB)',         'Skukuza Airport (SZK)',                 '2026-08-05 07:00:00', '2026-08-05 08:30:00',  750.00, 20),
('Maldivian',             'Velana International (MLE)',            'Gan International (GAN)',               '2026-08-10 09:00:00', '2026-08-10 10:15:00',  500.00, 25),
('Alitalia',              'Fiumicino (FCO)',                       'Venice Marco Polo (VCE)',               '2026-08-15 07:30:00', '2026-08-15 08:45:00',  220.00, 70),
('Qantas',                'Sydney Kingsford Smith (SYD)',          'Cairns Airport (CNS)',                  '2026-08-20 06:00:00', '2026-08-20 09:15:00',  480.00, 55),
('Royal Air Maroc',       'Mohammed V International (CMN)',        'Marrakech Menara (RAK)',                '2026-09-01 11:00:00', '2026-09-01 12:05:00',  190.00, 90),
('Swiss International',   'Zurich Airport (ZRH)',                  'Geneva Airport (GVA)',                  '2026-09-05 07:15:00', '2026-09-05 08:05:00',  180.00, 65),
('Thai Airways',          'Suvarnabhumi (BKK)',                    'Phuket International (HKT)',            '2026-09-10 14:00:00', '2026-09-10 15:20:00',  260.00, 75),
('Kenya Airways',         'JKIA Nairobi (NBO)',                   'Abeid Amani Karume (ZNZ)',              '2026-09-15 08:30:00', '2026-09-15 09:45:00',  380.00, 48),
('Emirates',              'Dubai International (DXB)',             'OR Tambo International (JNB)',          '2026-10-01 23:00:00', '2026-10-02 07:30:00', 6200.00, 22),
('British Airways',       'Heathrow (LHR)',                       'Narita International (NRT)',             '2026-10-05 11:00:00', '2026-10-06 07:30:00', 9800.00, 15),
('Air France',            'Charles de Gaulle (CDG)',               'Suvarnabhumi (BKK)',                   '2026-10-10 22:00:00', '2026-10-11 14:30:00', 7500.00, 28),
('Delta Airlines',        'Hartsfield-Jackson (ATL)',              'JFK International (JFK)',              '2026-11-01 06:00:00', '2026-11-01 09:15:00',  320.00, 80),
('Etihad Airways',        'Abu Dhabi International (AUH)',         'Velana International (MLE)',            '2026-11-10 03:00:00', '2026-11-10 07:45:00', 4200.00, 35);

-- -------------------------------------------------------
-- accomodation  (15 accommodations)
-- -------------------------------------------------------
INSERT INTO accomodation (name, type, address, rating, pricePerNight, ammenities) VALUES
('Table Bay Hotel',          'Hotel',   'Quay 6, V&A Waterfront, Cape Town, ZA',          4.8,  4500.00, 'Pool,Spa,Restaurant,WiFi,Concierge'),
('Hotel de Crillon',         'Hotel',   '10 Place de la Concorde, Paris, FR',             4.9, 12000.00, 'Michelin Restaurant,Spa,Butler Service,WiFi'),
('COMO Uma Ubud',            'Resort',  'Jalan Raya Sanggingan, Ubud, Bali, ID',          4.7,  3800.00, 'Infinity Pool,Yoga,Spa,Organic Restaurant,WiFi'),
('The Standard New York',    'Hotel',   '848 Washington St, New York, US',                4.3,  6500.00, 'Rooftop Bar,Gym,WiFi,Restaurant'),
('Park Hyatt Tokyo',         'Hotel',   '3-7-1-2 Nishi-Shinjuku, Tokyo, JP',             4.8,  9000.00, 'Pool,Spa,Multiple Restaurants,WiFi,Concierge'),
('Mystique Santorini',       'Boutique','Oia, 84702, Santorini, GR',                      4.9,  8500.00, 'Infinity Pool,Caldera Views,Spa,WiFi'),
('Inkaterra Machu Picchu',   'Lodge',   'Aguas Calientes, Machu Picchu, PE',             4.6,  5200.00, 'Garden,Restaurant,WiFi,Nature Walks'),
('Lion Sands Game Reserve',  'Lodge',   'Kruger National Park, Limpopo, ZA',              4.9,  9500.00, 'Bush Walks,Game Drives,Pool,All Inclusive'),
('Gili Lankanfushi',         'Resort',  'Lankanfushi Island, North Male, MV',             4.9, 15000.00, 'Overwater Bungalows,Diving,Spa,WiFi,All Inclusive'),
('Hotel Hassler Roma',       'Hotel',   'Piazza Trinita dei Monti 6, Rome, IT',           4.7,  7800.00, 'Rooftop Restaurant,Spa,WiFi,Concierge'),
('Quay Hotel Sydney',        'Hotel',   'Upper Level, Overseas Passenger Terminal, SYD', 4.6,  5600.00, 'Harbour Views,Restaurant,Bar,WiFi,Gym'),
('La Mamounia',              'Hotel',   'Avenue Bab Jdid, Marrakech, MA',                 4.9, 11000.00, 'Pool,Spa,Casino,Gardens,Multiple Restaurants,WiFi'),
('The Dolder Grand Zurich',  'Hotel',   'Kurhausstrasse 65, Zurich, CH',                  4.8,  9200.00, 'Spa,Pool,Golf,Fine Dining,WiFi'),
('Capella Bangkok',          'Hotel',   '300/2 Charoenkrung Rd, Bangkok, TH',             4.8,  8800.00, 'River Views,Infinity Pool,Spa,Restaurants,WiFi'),
('Zuri Zanzibar',            'Resort',  'Kendwa Beach, Zanzibar, TZ',                     4.5,  3500.00, 'Beach,Pool,Diving,Restaurant,WiFi');

-- -------------------------------------------------------
-- touristAttraction  (20 attractions)
-- -------------------------------------------------------
INSERT INTO touristAttraction (name, category, description, ticketPrice) VALUES
('Table Mountain Aerial Cableway',  'Nature',       'Iconic rotating cable car to the flat-topped Table Mountain summit.',        360.00),
('Eiffel Tower',                    'Landmark',     'Iron lattice tower on the Champ de Mars, symbol of Paris.',                  260.00),
('Tanah Lot Temple',                'Cultural',     'Ancient Hindu shrine perched on a rock formation in the sea.',                60.00),
('Statue of Liberty',               'Landmark',     'Colossal neoclassical sculpture on Liberty Island, New York.',               240.00),
('Senso-ji Temple',                 'Cultural',     'Ancient Buddhist temple in Asakusa, Tokyo - oldest temple in the city.',       0.00),
('Akrotiri Archaeological Site',    'Historical',   'Prehistoric settlement buried by the Minoan eruption on Santorini.',         140.00),
('Machu Picchu Citadel',            'Historical',   'The Lost City of the Incas - UNESCO World Heritage site in the Andes.',      700.00),
('Kruger National Park Safari',     'Wildlife',     'Self-drive or guided safari through Africa''s premier wildlife reserve.',     450.00),
('Manta Ray Night Snorkel',         'Adventure',    'Night snorkelling with majestic manta rays in Maldivian waters.',           1200.00),
('Colosseum Rome',                  'Historical',   'Iconic elliptical amphitheatre at the heart of ancient Rome.',               180.00),
('Sydney Opera House Tour',         'Culture',      'Guided behind-the-scenes tour of the UNESCO World Heritage venue.',          450.00),
('Djemaa el-Fna Market',            'Cultural',     'Buzzing central square with storytellers, musicians and food stalls.',         0.00),
('Rhine Falls Boat Trip',           'Nature',       'Close-up boat excursion to the largest waterfall in Europe.',               350.00),
('Grand Palace Bangkok',            'Historical',   'Opulent royal complex and home of the Emerald Buddha.',                     500.00),
('Prison Island Zanzibar',          'Historical',   'Island with giant tortoises and ruins of a former British prison.',         250.00),
('Robben Island Tour',              'Historical',   'UNESCO site where Nelson Mandela was imprisoned for 18 years.',             450.00),
('Louvre Museum',                   'Culture',      'World''s largest art museum housing the Mona Lisa and Venus de Milo.',        170.00),
('Ubud Monkey Forest',              'Nature',       'Sacred sanctuary home to over 700 Balinese long-tailed macaques.',          100.00),
('Times Square',                    'Landmark',     'Iconic commercial intersection known as the Crossroads of the World.',         0.00),
('Fushimi Inari Taisha',            'Cultural',     'Shinto shrine famous for its thousands of vermilion torii gates.',            0.00);

-- -------------------------------------------------------
-- restaurant  (20 restaurants, linked to destinations)
-- -------------------------------------------------------
INSERT INTO restaurant (name, cuisineType, address, rating, priceRange, destinationID) VALUES
('The Test Kitchen',      'Modern African', '375 Albert Rd, Woodstock, Cape Town',          4.9, '$$$$', 1),
('La Colombe',            'French Fusion',  'Silvermist Mountain Lodge, Cape Town',          4.8, '$$$$', 1),
('Le Jules Verne',        'French',         'Eiffel Tower, Champ de Mars, Paris',            4.7, '$$$$', 2),
('L''Ambroisie',          'French',         '9 Place des Vosges, Paris',                     4.9, '$$$$', 2),
('Locavore',              'Indonesian',     'Jalan Dewi Sita 10, Ubud, Bali',               4.8, '$$$',  3),
('Mozaic Restaurant',     'French-Asian',   'Jalan Raya Sanggingan, Ubud, Bali',             4.7, '$$$$', 3),
('Eleven Madison Park',   'Contemporary',   '11 Madison Ave, New York City',                 4.8, '$$$$', 4),
('Katz''s Delicatessen',  'American Deli',  '205 E Houston St, New York City',               4.5, '$$',   4),
('Sukiyabashi Jiro',      'Sushi',          'Tsukamoto Sogyo Bldg, Chuo-ku, Tokyo',         4.9, '$$$$', 5),
('Ichiran Ramen',         'Japanese',       '1-22-7 Jinnan, Shibuya, Tokyo',                 4.6, '$$',   5),
('Ambrosia Santorini',    'Mediterranean',  'Oia, Santorini',                                4.7, '$$$',  6),
('Metaxy Mas',            'Greek',          'Exo Gialos, Santorini',                         4.6, '$$$',  6),
('Indio Feliz',           'French-Peruvian','Av Pachacutec 103, Aguas Calientes',            4.5, '$$$',  7),
('Jemaa el-Fna Food Stalls','Moroccan',     'Djemaa el-Fna Square, Marrakech',              4.4, '$',    12),
('Al Fassia',             'Moroccan',       'Avenue Mohammed V, Marrakech',                  4.7, '$$$',  12),
('Aria Restaurant Sydney','Modern Australian','1 Macquarie St, Sydney',                      4.7, '$$$$', 11),
('The Boathouse',         'Seafood',        'Palm Beach, Sydney',                            4.5, '$$$',  11),
('Bo.lan',                'Thai',           '24 Sukhumvit 53, Bangkok',                      4.7, '$$$',  14),
('Gaggan Anand',          'Progressive Indian','68/1 Soi Langsuan, Bangkok',                 4.9, '$$$$', 14),
('The Rock Restaurant',   'Seafood',        'Michamvi Pingwe, Zanzibar',                     4.6, '$$$',  15);

-- -------------------------------------------------------
-- travelPackage  (15 packages)
-- -------------------------------------------------------
INSERT INTO travelPackage (agencyID, title, description, basePrice, durationDays, startDate, endDate, itinerary, status, dateCreated, packageType) VALUES
-- Agency 1 - SunWay Travels
(1, 'Cape Town & Winelands Escape',        'Explore the Mother City, Table Mountain and the world-class Cape Winelands.',                 18500.00,  7, '2026-07-01', '2026-07-07', 'Day 1: Arrive CPT. Day 2-3: Table Mountain & City Bowl. Day 4-5: Cape Winelands. Day 6: Cape Point. Day 7: Depart.',              'active', '2026-01-15', 'regular'),
(1, 'Garden Route Adventure',              'Drive South Africa''s most scenic coastal route from Mossel Bay to Storms River.',            22000.00,  10,'2026-08-01', '2026-08-10', 'Day 1: JNB-CPT. Day 2: Mossel Bay. Day 3: Wilderness. Day 4: Knysna. Day 5-6: Tsitsikamma. Day 7-9: Port Elizabeth. Day 10: Fly.','active', '2026-01-20', 'regular'),
-- Agency 2 - Globe Trekkers
(2, 'Europe on a Shoestring',              'Hit 5 European capitals in 14 days without breaking the bank.',                               32000.00,  14,'2026-09-01', '2026-09-14', 'London->Paris->Amsterdam->Berlin->Prague. 2-3 nights per city, budget hostels, rail passes included.',                              'active', '2026-02-01', 'regular'),
-- Agency 3 - Azure Escapes
(3, 'Greek Island Hopper',                 'Santorini, Mykonos and Rhodes - sun, sea and ancient history.',                               41500.00,  10,'2026-07-20', '2026-07-29', 'Day 1-4: Santorini. Day 5-7: Mykonos. Day 8-10: Rhodes.',                                                                        'active', '2026-02-10', 'regular'),
(3, 'Romantic Paris Weekend',              'A curated 4-night Paris getaway perfect for couples.',                                        28000.00,   4, '2026-08-15', '2026-08-18', 'Day 1: Arrive CDG, Seine cruise. Day 2: Louvre, Eiffel Tower. Day 3: Versailles. Day 4: Depart.',                                 'active', '2026-02-14', 'regular'),
-- Agency 4 - Horizon Adventures
(4, 'Inca Trail & Machu Picchu Trek',      'Four-day classic Inca Trail ending at the Sun Gate of Machu Picchu at sunrise.',             55000.00,   8, '2026-08-01', '2026-08-08', 'Day 1: Cusco acclimatisation. Day 2-5: Inca Trail trekking. Day 6: Machu Picchu. Day 7: Cusco. Day 8: Fly Lima.',                'active', '2026-02-20', 'regular'),
-- Agency 5 - Pearl Voyages
(5, 'Bali Serenity Retreat',               'Wellness and culture in the Island of the Gods - yoga, cooking classes and temple visits.',   29500.00,   8, '2026-07-10', '2026-07-17', 'Day 1: Arrive DPS. Day 2-3: Ubud rice terraces & temples. Day 4-5: Yoga retreat. Day 6-7: Seminyak beach. Day 8: Depart.',     'active', '2026-03-01', 'regular'),
(5, 'Maldives Overwater Luxury',           'Five nights in an overwater bungalow with snorkelling, diving and sunset cruises.',           95000.00,   6, '2026-08-10', '2026-08-15', 'Day 1: Arrive MLE, speedboat transfer. Day 2-5: Diving, snorkelling, spa. Day 6: Depart.',                                       'active', '2026-03-05', 'regular'),
-- Agency 6 - Velvet Journeys
(6, 'Paris & French Riviera Luxury',       'The ultimate French luxury experience from Paris to Nice and Monaco.',                        88000.00,  10, '2026-09-05', '2026-09-14', 'Day 1-4: Paris. Day 5: TGV to Nice. Day 6-8: French Riviera. Day 9: Monaco. Day 10: Depart.',                                    'active', '2026-03-10', 'regular'),
-- Agency 8 - Coral Coast Tours
(8, 'Bangkok & Thai Islands',              'Bangkok temples and street food followed by crystal-clear island paradise.',                  35000.00,  10, '2026-09-10', '2026-09-19', 'Day 1-3: Bangkok temples and markets. Day 4: Fly to Koh Samui. Day 5-9: Island hopping. Day 10: Depart.',                       'active', '2026-03-20', 'regular'),
-- Agency 9 - Alpine Quest
(9, 'Swiss Alps Ski Safari',               'A week of world-class skiing across Zermatt, Verbier and St. Moritz.',                        72000.00,   7, '2026-12-20', '2026-12-26', 'Day 1: Arrive ZRH. Day 2-3: Zermatt. Day 4-5: Verbier. Day 6-7: St. Moritz.',                                                    'active', '2026-04-01', 'regular'),
-- Agency 10 - Savanna Trails
(10,'Big Five Kruger Safari',              'Immersive 5-day safari in the Kruger with expert trackers and all-inclusive bush lodge.', 48000.00,   5, '2026-08-05', '2026-08-09', 'Day 1: Fly JNB-SZK. Day 2-4: Morning and evening game drives. Day 5: Fly back.',                                                   'active', '2026-04-05', 'regular'),
(10,'Cape Town & Kruger Combo',            'The best of South Africa - vibrant Cape Town and the wild Kruger Park.',                      62000.00,  12, '2026-09-01', '2026-09-12', 'Day 1-5: Cape Town (Table Mountain, Winelands, Cape Point). Day 6: Fly to Kruger. Day 7-11: Safari. Day 12: Fly home.',          'active', '2026-04-10', 'regular'),
-- Group trips
(4, 'Kilimanjaro Group Summit Challenge',  'Join a guided group climb up Africa''s highest peak through 5 climate zones.',               68000.00,  10, '2026-08-15', '2026-08-24', 'Day 1: Arrive Arusha. Day 2-8: Ascent via Machame route. Day 9: Descent & celebration. Day 10: Fly.',                             'active', '2026-04-15', 'group'),
(2, 'Tokyo & Kyoto Group Cultural Tour',   'Explore Japan''s ancient temples and futuristic cities with fellow travel enthusiasts.',       55000.00,   9, '2026-10-05', '2026-10-13', 'Day 1-4: Tokyo (Senso-ji, Shibuya, Akihabara). Day 5: Shinkansen to Kyoto. Day 6-8: Kyoto temples & geisha district. Day 9: Fly.',  'active', '2026-04-20', 'group');

-- -------------------------------------------------------
-- RegularPackage  (for all non-group packages)
-- -------------------------------------------------------
INSERT INTO RegularPackage (packageID) VALUES (1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12),(13);

-- -------------------------------------------------------
-- GroupTrip  (for group packages)
-- -------------------------------------------------------
INSERT INTO GroupTrip (packageID, minGroupSize, maxGroupSize, currentGroupSize, groupDeadline, groupDescription) VALUES
(14,  6, 18,  9, '2026-08-01', 'Join fellow adventurers for a guided Kilimanjaro summit. All fitness levels welcome; training plan provided.'),
(15,  4, 20, 12, '2026-09-25', 'A cultural group immersion into Japan. Guided by a Japanese-speaking expert. Meals and transport included.');

-- -------------------------------------------------------
-- PackageDestination
-- -------------------------------------------------------
INSERT INTO PackageDestination (packageID, destinationID) VALUES
(1,  1),(2,  1),                        -- Cape Town packages -> Cape Town
(3,  2),(3, 10),(3, 13),                -- Europe Shoestring -> Paris, Rome, Zurich (sample)
(4,  6),                                -- Greek Island -> Santorini
(5,  2),                                -- Paris Weekend -> Paris
(6,  7),                                -- Inca Trail -> Machu Picchu
(7,  3),                                -- Bali Retreat -> Bali
(8,  9),                                -- Maldives -> Maldives
(9,  2),                                -- Paris & Riviera -> Paris
(10, 14),                               -- Bangkok -> Bangkok
(11, 13),                               -- Swiss Alps -> Zurich
(12, 8),                                -- Kruger -> Kruger
(13, 1),(13, 8),                        -- Combo -> Cape Town + Kruger
(14, 15),                               -- Kilimanjaro -> Zanzibar (base destination)
(15, 5);                                -- Tokyo tour -> Tokyo

-- -------------------------------------------------------
-- PackageFlight
-- -------------------------------------------------------
INSERT INTO PackageFlight (packageID, flightID) VALUES
(1,  1),(1,  2),
(2,  1),
(3,  2),
(4,  6),
(5,  2),
(6,  7),
(7,  3),
(8,  9),(8, 20),
(9,  2),
(10,14),
(11,13),
(12, 8),
(13, 1),(13, 8),
(14,15),
(15,17);

-- -------------------------------------------------------
-- PackageAccommodation
-- -------------------------------------------------------
INSERT INTO PackageAccommodation (packageID, accomodationID, staysAt) VALUES
(1,  1,  6),
(2,  1,  9),
(3,  2,  3),
(4,  6,  9),
(5,  2,  3),
(6,  7,  6),
(7,  3,  7),
(8,  9,  5),
(9,  2,  4),(9, 12, 5),
(10,14,  8),
(11,13,  6),
(12, 8,  4),
(13, 1,  4),(13, 8, 7),
(14,15,  8),
(15, 5,  8);

-- -------------------------------------------------------
-- PackageAttraction
-- -------------------------------------------------------
INSERT INTO PackageAttraction (packageID, attractionID) VALUES
(1,  1),(1, 16),
(2,  1),
(3, 17),
(4,  6),
(5,  2),(5, 17),
(6,  7),
(7,  3),(7, 18),
(8,  9),
(9,  2),(9, 17),
(10,14),
(11,13),
(12, 8),
(13, 1),(13, 8),
(15, 5),(15,20);

-- -------------------------------------------------------
-- DestinationAttraction
-- -------------------------------------------------------
INSERT INTO DestinationAttraction (destinationID, attractionID) VALUES
(1,  1),(1, 16),            -- Cape Town
(2,  2),(2, 17),            -- Paris
(3,  3),(3, 18),            -- Bali
(4,  4),(4, 19),            -- NYC
(5,  5),(5, 20),            -- Tokyo
(6,  6),                    -- Santorini
(7,  7),                    -- Machu Picchu
(8,  8),                    -- Kruger
(9,  9),                    -- Maldives
(10,10),                    -- Rome
(11,11),                    -- Sydney
(12,12),                    -- Marrakech
(13,13),                    -- Zurich
(14,14),                    -- Bangkok
(15,15);                    -- Zanzibar

-- -------------------------------------------------------
-- booking  (25 bookings)
-- -------------------------------------------------------
INSERT INTO booking (travellerID, packageID, bookingDate, numTravellers, totalAmount, status, paymentStatus) VALUES
(1,  1, '2026-03-10',  2,  37000.00, 'confirmed', 'paid'),
(2,  6, '2026-03-15',  1,  55000.00, 'confirmed', 'paid'),
(3,  5, '2026-03-20',  2,  56000.00, 'confirmed', 'paid'),
(4,  6, '2026-03-25',  1,  55000.00, 'confirmed', 'paid'),
(5, 15, '2026-03-28',  1,  55000.00, 'confirmed', 'paid'),
(6, 10, '2026-04-01',  2,  70000.00, 'confirmed', 'paid'),
(7, 12, '2026-04-05',  1,  48000.00, 'confirmed', 'paid'),
(8,  8, '2026-04-08',  2, 190000.00, 'confirmed', 'paid'),
(9,  2, '2026-04-10',  1,  22000.00, 'confirmed', 'paid'),
(10, 3, '2026-04-12',  1,  32000.00, 'confirmed', 'paid'),
(11, 7, '2026-04-15',  1,  29500.00, 'confirmed', 'paid'),
(12,14, '2026-04-18',  1,  68000.00, 'pending',   'unpaid'),
(13, 9, '2026-04-20',  2, 176000.00, 'confirmed', 'paid'),
(14, 4, '2026-04-22',  2,  83000.00, 'confirmed', 'paid'),
(15, 1, '2026-04-25',  1,  18500.00, 'confirmed', 'paid'),
(16, 7, '2026-04-28',  2,  59000.00, 'confirmed', 'paid'),
(17,12, '2026-05-01',  2,  96000.00, 'confirmed', 'paid'),
(18,10, '2026-05-03',  1,  35000.00, 'pending',   'unpaid'),
(19, 9, '2026-05-05',  1,  88000.00, 'confirmed', 'paid'),
(20,13, '2026-05-07',  2, 124000.00, 'confirmed', 'paid'),
(1,  8, '2026-05-08',  1,  95000.00, 'pending',   'unpaid'),
(3, 11, '2026-05-09',  2, 144000.00, 'confirmed', 'paid'),
(5,  4, '2026-05-10',  1,  41500.00, 'confirmed', 'paid'),
(7, 15, '2026-05-11',  1,  55000.00, 'cancelled', 'refunded'),
(9,  6, '2026-05-12',  1,  55000.00, 'confirmed', 'paid');

-- -------------------------------------------------------
-- TravellerGroupTrip
-- -------------------------------------------------------
INSERT INTO TravellerGroupTrip (travellerID, packageID, joinedDate) VALUES
(2,  14, '2026-04-16'),
(5,  15, '2026-03-28'),
(6,  14, '2026-04-19'),
(7,  15, '2026-05-11'),
(9,  14, '2026-04-25'),
(11, 15, '2026-04-18'),
(12, 14, '2026-04-20'),
(14, 15, '2026-04-22'),
(17, 14, '2026-05-02'),
(18, 15, '2026-05-04'),
(1,  15, '2026-05-08'),
(3,  14, '2026-05-09');

-- -------------------------------------------------------
-- review  (30 reviews)
-- -------------------------------------------------------
INSERT INTO review (travellerID, targetType, packageID, agencyID, rating, comment, reviewDate) VALUES
(1,  'package', 1,  NULL, 4.8, 'Absolutely stunning Cape Town experience. The winelands tour was a highlight!',                              '2026-07-09'),
(2,  'package', 6,  NULL, 4.9, 'The Inca Trail was life-changing. Our guide was exceptional and the sunrise at the Sun Gate was magical.', '2026-08-10'),
(3,  'package', 5,  NULL, 4.7, 'Paris was dreamy. Hotel de Crillon exceeded expectations. Would return in a heartbeat.',                     '2026-08-20'),
(4,  'package', 6,  NULL, 4.6, 'Great trek but be prepared - it is physically demanding. The agency support was excellent.',                 '2026-08-10'),
(5,  'package', 15, NULL, 4.8, 'Japan group tour was perfectly organised. Loved meeting fellow travellers with the same passion.',           '2026-10-15'),
(6,  'package', 10, NULL, 4.5, 'Bangkok street food was unreal! Koh Samui beach days were the perfect contrast.',                           '2026-09-21'),
(7,  'package', 12, NULL, 4.9, 'Lion Sands was incredible. Saw all the Big Five in just 3 days. Totally worth every cent.',                  '2026-08-11'),
(8,  'package', 8,  NULL, 5.0, 'The Maldives overwater bungalow was a dream. Best holiday of my life, no question.',                        '2026-08-17'),
(9,  'package', 2,  NULL, 4.4, 'Great scenic drive but the itinerary felt slightly rushed at some stops.',                                   '2026-08-12'),
(10, 'package', 3,  NULL, 4.3, 'Good value for 5 cities in 2 weeks. Accommodation varies but overall a solid trip.',                        '2026-09-16'),
(11, 'package', 7,  NULL, 4.7, 'Ubud yoga retreat was exactly what I needed. Peaceful, spiritual and rejuvenating.',                        '2026-07-19'),
(13, 'package', 9,  NULL, 4.9, 'Velvet Journeys went above and beyond. Paris to Monaco in style - unforgettable.',                          '2026-09-16'),
(14, 'package', 4,  NULL, 4.8, 'Santorini was as beautiful as the photos. Mystique hotel has the most incredible infinity pool.',            '2026-07-31'),
(15, 'package', 1,  NULL, 4.6, 'Lovely Cape Town package. Table Mountain hike was breathtaking. Winelands tasting was superb.',             '2026-07-09'),
(16, 'package', 7,  NULL, 4.5, 'Bali was amazing. Loved the Monkey Forest and cooking class. Hotel could be slightly better value.',         '2026-07-19'),
(17, 'package', 12, NULL, 4.8, 'Kruger safari far exceeded expectations. Incredible wildlife encounters every single day.',                   '2026-08-11'),
(19, 'package', 9,  NULL, 4.7, 'French Riviera is absolutely stunning. Velvet Journeys curated a flawless luxury experience.',              '2026-09-16'),
(20, 'package', 13, NULL, 4.6, 'South Africa combo is brilliant - Cape Town''s vibrancy then Kruger''s raw wildlife. Highly recommend.',      '2026-09-14'),
-- Agency reviews
(1,  'agency',  NULL,  1,  4.8, 'SunWay Travels is professional, responsive and truly passionate about South African tourism.',              '2026-07-10'),
(2,  'agency',  NULL,  4,  4.9, 'Horizon Adventures staff are knowledgeable, safety-conscious and incredibly supportive on the trail.',      '2026-08-12'),
(3,  'agency',  NULL,  3,  4.7, 'Azure Escapes made our Paris trip effortless. Every detail was perfectly arranged.',                        '2026-08-21'),
(5,  'agency',  NULL,  2,  4.6, 'Globe Trekkers created a wonderful group atmosphere. Great for solo travellers too.',                       '2026-10-16'),
(6,  'agency',  NULL,  8,  4.5, 'Coral Coast Tours knows Southeast Asia inside out. Excellent restaurant recommendations.',                  '2026-09-22'),
(7,  'agency',  NULL, 10,  4.9, 'Savanna Trails is simply the best safari operator in South Africa. Expert guides, top lodges.',             '2026-08-12'),
(8,  'agency',  NULL,  5,  5.0, 'Pearl Voyages delivered pure perfection. The Maldives package was worth every cent.',                       '2026-08-18'),
(9,  'agency',  NULL,  1,  4.3, 'Good agency overall but communication could be slightly faster before the trip.',                           '2026-08-13'),
(11, 'agency',  NULL,  5,  4.8, 'Pearl Voyages'' Bali package had brilliant local guides who truly enhanced the experience.',                '2026-07-20'),
(13, 'agency',  NULL,  6,  4.9, 'Velvet Journeys redefined what luxury travel means. Every moment felt curated and special.',                '2026-09-17'),
(14, 'agency',  NULL,  3,  4.8, 'Azure Escapes'' Santorini package was flawless from booking to departure. Will use again.',                '2026-08-01'),
(17, 'agency',  NULL, 10,  4.7, 'Savanna Trails staff are passionate and professional. The Kruger experience was world-class.',              '2026-08-12');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  END OF DATA POPULATION SCRIPT
-- ============================================================
