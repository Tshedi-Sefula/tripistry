-- ============================================================
--  Tripistry - Full Database Script
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
USE tripistry;

-- ============================================================
--  DROP ALL TABLES (clean slate)
-- ============================================================
DROP TABLE IF EXISTS AgencyManagesGroupTripMember;
DROP TABLE IF EXISTS GroupTripMember;
DROP TABLE IF EXISTS PackageAttraction;
DROP TABLE IF EXISTS PackageAccommodation;
DROP TABLE IF EXISTS PackageFlight;
DROP TABLE IF EXISTS PackageDestination;
DROP TABLE IF EXISTS Review;
DROP TABLE IF EXISTS Booking;
DROP TABLE IF EXISTS GroupTrip;
DROP TABLE IF EXISTS RegularPackage;
DROP TABLE IF EXISTS TravelPackage;
DROP TABLE IF EXISTS Restaurant;
DROP TABLE IF EXISTS TouristAttraction;
DROP TABLE IF EXISTS AccommodationAmenity;
DROP TABLE IF EXISTS Accommodation;
DROP TABLE IF EXISTS Flight;
DROP TABLE IF EXISTS Destination;
DROP TABLE IF EXISTS TravellerPreference;
DROP TABLE IF EXISTS Traveller;
DROP TABLE IF EXISTS TravelAgency;
DROP TABLE IF EXISTS User;

-- ============================================================
--  1. USER SUPERTYPE
-- ============================================================
CREATE TABLE User (
    userID       INT          NOT NULL AUTO_INCREMENT,
    email        VARCHAR(150) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    phone        VARCHAR(25),
    joinDate     DATE         NOT NULL DEFAULT (CURDATE()),
    role         ENUM('traveller','agency') NOT NULL,
    PRIMARY KEY (userID)
) ENGINE=InnoDB;

-- ============================================================
--  2. SUBTYPES
-- ============================================================
CREATE TABLE TravelAgency (
    userID      INT           NOT NULL,
    name        VARCHAR(120)  NOT NULL,
    description TEXT,
    website     VARCHAR(200),
    rating      DECIMAL(3,2)  DEFAULT 0.00 CHECK (rating BETWEEN 0 AND 5),
    address     VARCHAR(300),
    PRIMARY KEY (userID),
    CONSTRAINT fk_agency_user FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Traveller (
    userID        INT         NOT NULL,
    firstName     VARCHAR(60) NOT NULL,
    lastName      VARCHAR(60) NOT NULL,
    passportNum   VARCHAR(30) UNIQUE,
    nationality   VARCHAR(80),
    DOB           DATE,
    PRIMARY KEY (userID),
    CONSTRAINT fk_traveller_user FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Multivalued attribute: preferences
CREATE TABLE TravellerPreference (
    userID     INT          NOT NULL,
    preference VARCHAR(80)  NOT NULL,
    PRIMARY KEY (userID, preference),
    CONSTRAINT fk_pref_traveller FOREIGN KEY (userID) REFERENCES Traveller(userID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  3. ROLE ENFORCEMENT TRIGGERS
-- ============================================================


-- ============================================================
--  4. TRAVEL CONTENT
-- ============================================================
CREATE TABLE Destination (
    destinationID  INT           NOT NULL AUTO_INCREMENT,
    name           VARCHAR(120)  NOT NULL,
    country        VARCHAR(80)   NOT NULL,
    description    TEXT,
    latitude       DECIMAL(9,6),
    longitude      DECIMAL(9,6),
    popularityScore INT          DEFAULT 0,
    PRIMARY KEY (destinationID)
) ENGINE=InnoDB;

CREATE TABLE Flight (
    flightID           INT           NOT NULL AUTO_INCREMENT,
    departureAirport   VARCHAR(100)  NOT NULL,
    arrivalAirport     VARCHAR(100)  NOT NULL,
    departureDatetime  DATETIME      NOT NULL,
    arrivalDatetime    DATETIME      NOT NULL,
    price              DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    airline            VARCHAR(100)  NOT NULL,
    seatsAvailable     INT           NOT NULL DEFAULT 0 CHECK (seatsAvailable >= 0),
    PRIMARY KEY (flightID)
) ENGINE=InnoDB;

CREATE TABLE Accommodation (
    accommodationID INT           NOT NULL AUTO_INCREMENT,
    name            VARCHAR(120)  NOT NULL,
    type            VARCHAR(60),
    address         VARCHAR(300),
    pricePerNight   DECIMAL(10,2) NOT NULL CHECK (pricePerNight >= 0),
    rating          DECIMAL(3,2)  DEFAULT 0.00 CHECK (rating BETWEEN 0 AND 5),
    PRIMARY KEY (accommodationID)
) ENGINE=InnoDB;

-- Multivalued attribute: amenities
CREATE TABLE AccommodationAmenity (
    accommodationID INT         NOT NULL,
    amenity         VARCHAR(80) NOT NULL,
    PRIMARY KEY (accommodationID, amenity),
    CONSTRAINT fk_amenity_accommodation FOREIGN KEY (accommodationID)
        REFERENCES Accommodation(accommodationID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE TouristAttraction (
    attractionID INT           NOT NULL AUTO_INCREMENT,
    name         VARCHAR(120)  NOT NULL,
    description  TEXT,
    category     VARCHAR(80),
    ticketPrice  DECIMAL(10,2) DEFAULT 0.00 CHECK (ticketPrice >= 0),
    PRIMARY KEY (attractionID)
) ENGINE=InnoDB;

CREATE TABLE Restaurant (
    restaurantID  INT           NOT NULL AUTO_INCREMENT,
    destinationID INT           NOT NULL,
    name          VARCHAR(120)  NOT NULL,
    cuisineType   VARCHAR(80),
    address       VARCHAR(300),
    rating        DECIMAL(3,2)  DEFAULT 0.00 CHECK (rating BETWEEN 0 AND 5),
    priceRange    VARCHAR(10)   CHECK (priceRange IN ('$','$$','$$$','$$$$')),
    PRIMARY KEY (restaurantID),
    CONSTRAINT fk_restaurant_destination FOREIGN KEY (destinationID)
        REFERENCES Destination(destinationID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  5. TRAVEL PACKAGE (supertype) + SUBTYPES
-- ============================================================
CREATE TABLE TravelPackage (
    packageID    INT            NOT NULL AUTO_INCREMENT,
    agencyUserID INT            NOT NULL,
    title        VARCHAR(200)   NOT NULL,
    description  TEXT,
    basePrice    DECIMAL(10,2)  NOT NULL CHECK (basePrice >= 0),
    durationDays INT            NOT NULL CHECK (durationDays > 0),
    itinerary    TEXT,
    status       ENUM('active','inactive','draft') DEFAULT 'active',
    startDate    DATE,
    endDate      DATE,
    dateCreated  DATE           NOT NULL DEFAULT (CURDATE()),
    PRIMARY KEY (packageID),
    CONSTRAINT fk_package_agency FOREIGN KEY (agencyUserID)
        REFERENCES TravelAgency(userID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE RegularPackage (
    packageID INT NOT NULL,
    PRIMARY KEY (packageID),
    CONSTRAINT fk_regular_package FOREIGN KEY (packageID)
        REFERENCES TravelPackage(packageID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE GroupTrip (
    packageID        INT           NOT NULL,
    minGroupSize     INT           NOT NULL DEFAULT 2 CHECK (minGroupSize >= 2),
    maxGroupSize     INT           NOT NULL CHECK (maxGroupSize >= minGroupSize),
    currentGroupSize INT           NOT NULL DEFAULT 0 CHECK (currentGroupSize >= 0),
    groupDeadline    DATE,
    groupDescription TEXT,
    PRIMARY KEY (packageID),
    CONSTRAINT fk_grouptrip_package FOREIGN KEY (packageID)
        REFERENCES TravelPackage(packageID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  6. BOOKING
-- ============================================================
CREATE TABLE Booking (
    bookingID       INT            NOT NULL AUTO_INCREMENT,
    travellerUserID INT            NOT NULL,
    packageID       INT            NOT NULL,
    bookingDate     DATE           NOT NULL DEFAULT (CURDATE()),
    numTravellers   INT            NOT NULL DEFAULT 1 CHECK (numTravellers >= 1),
    totalAmount     DECIMAL(12,2)  NOT NULL CHECK (totalAmount >= 0),
    status          ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    paymentStatus   ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    PRIMARY KEY (bookingID),
    CONSTRAINT fk_booking_traveller FOREIGN KEY (travellerUserID)
        REFERENCES Traveller(userID) ON DELETE RESTRICT,
    CONSTRAINT fk_booking_package FOREIGN KEY (packageID)
        REFERENCES TravelPackage(packageID) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  7. REVIEW (disjoint: targets package OR agency)
-- ============================================================
CREATE TABLE Review (
    reviewID        INT           NOT NULL AUTO_INCREMENT,
    travellerUserID INT           NOT NULL,
    rating          DECIMAL(3,2)  NOT NULL CHECK (rating BETWEEN 0 AND 5),
    comment         TEXT,
    reviewDate      DATE          NOT NULL DEFAULT (CURDATE()),
    targetType      ENUM('package','agency') NOT NULL,
    packageID       INT,
    agencyUserID    INT,
    PRIMARY KEY (reviewID),
    CONSTRAINT fk_review_traveller FOREIGN KEY (travellerUserID)
        REFERENCES Traveller(userID) ON DELETE CASCADE,
    CONSTRAINT fk_review_package FOREIGN KEY (packageID)
        REFERENCES TravelPackage(packageID) ON DELETE SET NULL,
    CONSTRAINT fk_review_agency FOREIGN KEY (agencyUserID)
        REFERENCES TravelAgency(userID) ON DELETE SET NULL
) ENGINE=InnoDB;



-- ============================================================
--  8. JUNCTION TABLES
-- ============================================================
CREATE TABLE PackageDestination (
    packageID     INT NOT NULL,
    destinationID INT NOT NULL,
    PRIMARY KEY (packageID, destinationID),
    CONSTRAINT fk_pd_package     FOREIGN KEY (packageID)     REFERENCES TravelPackage(packageID)  ON DELETE CASCADE,
    CONSTRAINT fk_pd_destination FOREIGN KEY (destinationID) REFERENCES Destination(destinationID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PackageFlight (
    packageID INT NOT NULL,
    flightID  INT NOT NULL,
    PRIMARY KEY (packageID, flightID),
    CONSTRAINT fk_pf_package FOREIGN KEY (packageID) REFERENCES TravelPackage(packageID) ON DELETE CASCADE,
    CONSTRAINT fk_pf_flight  FOREIGN KEY (flightID)  REFERENCES Flight(flightID)         ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PackageAccommodation (
    packageID       INT NOT NULL,
    accommodationID INT NOT NULL,
    staysAt         INT DEFAULT 1 CHECK (staysAt >= 1),
    PRIMARY KEY (packageID, accommodationID),
    CONSTRAINT fk_pa_package       FOREIGN KEY (packageID)       REFERENCES TravelPackage(packageID)      ON DELETE CASCADE,
    CONSTRAINT fk_pa_accommodation FOREIGN KEY (accommodationID) REFERENCES Accommodation(accommodationID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE PackageAttraction (
    packageID    INT NOT NULL,
    attractionID INT NOT NULL,
    PRIMARY KEY (packageID, attractionID),
    CONSTRAINT fk_pat_package    FOREIGN KEY (packageID)    REFERENCES TravelPackage(packageID)     ON DELETE CASCADE,
    CONSTRAINT fk_pat_attraction FOREIGN KEY (attractionID) REFERENCES TouristAttraction(attractionID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Traveller joins GroupTrip
CREATE TABLE GroupTripMember (
    packageID       INT NOT NULL,
    travellerUserID INT NOT NULL,
    joinedDate      DATE NOT NULL DEFAULT (CURDATE()),
    PRIMARY KEY (packageID, travellerUserID),
    CONSTRAINT fk_gtm_package   FOREIGN KEY (packageID)       REFERENCES GroupTrip(packageID)  ON DELETE CASCADE,
    CONSTRAINT fk_gtm_traveller FOREIGN KEY (travellerUserID) REFERENCES Traveller(userID)     ON DELETE CASCADE
) ENGINE=InnoDB;

-- Agency manages GroupTrip members
CREATE TABLE AgencyManagesGroupTripMember (
    packageID       INT NOT NULL,
    travellerUserID INT NOT NULL,
    agencyUserID    INT NOT NULL,
    PRIMARY KEY (packageID, travellerUserID, agencyUserID),
    CONSTRAINT fk_agm_package   FOREIGN KEY (packageID)       REFERENCES GroupTrip(packageID)   ON DELETE CASCADE,
    CONSTRAINT fk_agm_traveller FOREIGN KEY (travellerUserID) REFERENCES Traveller(userID)      ON DELETE CASCADE,
    CONSTRAINT fk_agm_agency    FOREIGN KEY (agencyUserID)    REFERENCES TravelAgency(userID)   ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  9. DATA POPULATION
-- ============================================================

-- User (10 agencies + 20 travellers = 30 users)
INSERT INTO User (email, passwordHash, phone, joinDate, role) VALUES
-- Agencies (userID 1-10)
('info@sunway.com',            '$2y$12$agencyHash001', '+27112345678',  '2020-03-15', 'agency'),
('hello@globetrekkers.io',     '$2y$12$agencyHash002', '+442079460958', '2019-07-22', 'agency'),
('book@azureescapes.com',      '$2y$12$agencyHash003', '+34915550123',  '2021-01-10', 'agency'),
('trips@horizonadv.co',        '$2y$12$agencyHash004', '+12125550199',  '2018-11-05', 'agency'),
('contact@pearlvoyages.com',   '$2y$12$agencyHash005', '+61299991234',  '2022-04-18', 'agency'),
('vip@velvetjourneys.com',     '$2y$12$agencyHash006', '+33142860000',  '2017-09-30', 'agency'),
('hi@terranomads.org',         '$2y$12$agencyHash007', '+4930123456',   '2020-06-01', 'agency'),
('tours@coralcoast.com',       '$2y$12$agencyHash008', '+6621234567',   '2021-08-14', 'agency'),
('ski@alpinequest.ch',         '$2y$12$agencyHash009', '+41441234567',  '2016-12-20', 'agency'),
('safari@savannatrails.co.za', '$2y$12$agencyHash010', '+27123456789',  '2019-02-28', 'agency'),
-- Travellers (userID 11-30)
('liam.johnson@email.com',    '$2y$12$travHash001', '+27821112222', '2024-01-10', 'traveller'),
('amara.nkosi@email.com',     '$2y$12$travHash002', '+27832223333', '2024-02-15', 'traveller'),
('emma.thompson@email.com',   '$2y$12$travHash003', '+44773334444', '2024-03-01', 'traveller'),
('carlos.rivera@email.com',   '$2y$12$travHash004', '+52554445555', '2024-03-20', 'traveller'),
('yuki.tanaka@email.com',     '$2y$12$travHash005', '+81905556666', '2024-04-05', 'traveller'),
('sofia.mendes@email.com',    '$2y$12$travHash006', '+55116667777', '2024-04-18', 'traveller'),
('ahmed.hassan@email.com',    '$2y$12$travHash007', '+20107778888', '2024-05-02', 'traveller'),
('mei.chen@email.com',        '$2y$12$travHash008', '+861388889999','2024-05-15', 'traveller'),
('oliver.smith@email.com',    '$2y$12$travHash009', '+61499990000', '2024-06-01', 'traveller'),
('nina.petrov@email.com',     '$2y$12$travHash010', '+79160001111', '2024-06-20', 'traveller'),
('kofi.asante@email.com',     '$2y$12$travHash011', '+233241112222','2024-07-10', 'traveller'),
('priya.sharma@email.com',    '$2y$12$travHash012', '+91982223333', '2024-07-25', 'traveller'),
('lucas.dubois@email.com',    '$2y$12$travHash013', '+33633334444', '2024-08-05', 'traveller'),
('isla.macleod@email.com',    '$2y$12$travHash014', '+44794445555', '2024-08-20', 'traveller'),
('diego.fernandez@email.com', '$2y$12$travHash015', '+34625556666', '2024-09-01', 'traveller'),
('hana.yamamoto@email.com',   '$2y$12$travHash016', '+81806667777', '2024-09-18', 'traveller'),
('ethan.brown@email.com',     '$2y$12$travHash017', '+13107778888', '2024-10-01', 'traveller'),
('zara.ali@email.com',        '$2y$12$travHash018', '+92300888999', '2024-10-15', 'traveller'),
('marco.rossi@email.com',     '$2y$12$travHash019', '+393330001111','2024-11-01', 'traveller'),
('fatima.o@email.com',        '$2y$12$travHash020', '+226701112222','2024-11-20', 'traveller');

-- TravelAgency (userID 1-10)
INSERT INTO TravelAgency (userID, name, description, website, rating, address) VALUES
(1,  'SunWay Travels',     'Premium South African travel specialist offering luxury safari and beach packages.',  'https://sunway.com',            4.50, '12 Rosebank Ave, Johannesburg, ZA'),
(2,  'Globe Trekkers',     'Budget-friendly round-the-world itineraries for the adventurous traveller.',         'https://globetrekkers.io',      4.20, '88 Oxford St, London, UK'),
(3,  'Azure Escapes',      'Mediterranean and European holiday packages with boutique hotel stays.',              'https://azureescapes.com',      4.70, 'Calle Gran Via 45, Madrid, ES'),
(4,  'Horizon Adventures', 'Outdoor adventure packages including trekking, camping and extreme sports.',          'https://horizonadv.co',         3.90, '500 5th Ave, New York, US'),
(5,  'Pearl Voyages',      'Asia-Pacific cruise and island-hopping specialists.',                                 'https://pearlvoyages.com',      4.60, '22 George St, Sydney, AU'),
(6,  'Velvet Journeys',    'Luxury European river cruises and chateau tours.',                                    'https://velvetjourneys.com',    4.80, '15 Rue de la Paix, Paris, FR'),
(7,  'Terra Nomads',       'Eco-tourism and sustainable travel experiences worldwide.',                           'https://terranomads.org',       4.10, 'Unter den Linden 10, Berlin, DE'),
(8,  'Coral Coast Tours',  'Southeast Asia beach and island packages at every budget.',                           'https://coralcoast.com',        4.30, '99 Sukhumvit Rd, Bangkok, TH'),
(9,  'Alpine Quest',       'Winter skiing and summer hiking packages across the Alps.',                           'https://alpinequest.ch',        4.55, 'Bahnhofstrasse 5, Zurich, CH'),
(10, 'Savanna Trails',     'African wildlife safari specialists covering the Big Five routes.',                   'https://savannatrails.co.za',   4.65, '8 Lynnwood Rd, Pretoria, ZA');

-- Traveller (userID 11-30)
INSERT INTO Traveller (userID, firstName, lastName, passportNum, nationality, DOB) VALUES
(11, 'Liam',    'Johnson',   'ZA1234567', 'South African', '1990-05-14'),
(12, 'Amara',   'Nkosi',     'ZA2345678', 'South African', '1995-08-22'),
(13, 'Emma',    'Thompson',  'GB3456789', 'British',       '1988-11-30'),
(14, 'Carlos',  'Rivera',    'MX4567890', 'Mexican',       '1993-02-17'),
(15, 'Yuki',    'Tanaka',    'JP5678901', 'Japanese',      '1997-06-05'),
(16, 'Sofia',   'Mendes',    'BR6789012', 'Brazilian',     '1991-09-19'),
(17, 'Ahmed',   'Hassan',    'EG7890123', 'Egyptian',      '1986-12-01'),
(18, 'Mei',     'Chen',      'CN8901234', 'Chinese',       '1999-03-25'),
(19, 'Oliver',  'Smith',     'AU9012345', 'Australian',    '1985-07-13'),
(20, 'Nina',    'Petrov',    'RU0123456', 'Russian',       '1994-10-28'),
(21, 'Kofi',    'Asante',    'GH1234568', 'Ghanaian',      '1992-04-03'),
(22, 'Priya',   'Sharma',    'IN2345679', 'Indian',        '1996-01-16'),
(23, 'Lucas',   'Dubois',    'FR3456780', 'French',        '1989-06-07'),
(24, 'Isla',    'MacLeod',   'GB4567891', 'Scottish',      '2000-09-14'),
(25, 'Diego',   'Fernandez', 'ES5678902', 'Spanish',       '1987-03-30'),
(26, 'Hana',    'Yamamoto',  'JP6789013', 'Japanese',      '1998-12-12'),
(27, 'Ethan',   'Brown',     'US7890124', 'American',      '1993-07-22'),
(28, 'Zara',    'Ali',       'PK8901235', 'Pakistani',     '2001-05-08'),
(29, 'Marco',   'Rossi',     'IT9012346', 'Italian',       '1984-02-20'),
(30, 'Fatima',  'Ouedraogo', 'BF0123457', 'Burkinabe',     '1997-08-11');

-- TravellerPreference
INSERT INTO TravellerPreference (userID, preference) VALUES
(11,'beach'),(11,'wildlife'),
(12,'adventure'),(12,'hiking'),
(13,'culture'),(13,'food'),
(14,'history'),(14,'architecture'),
(15,'anime'),(15,'food'),(15,'shopping'),
(16,'music'),(16,'beach'),(16,'nightlife'),
(17,'history'),(17,'deserts'),
(18,'shopping'),(18,'food'),(18,'luxury'),
(19,'surfing'),(19,'outdoor'),
(20,'ballet'),(20,'museums'),(20,'art'),
(21,'culture'),(21,'music'),
(22,'yoga'),(22,'temples'),
(23,'wine'),(23,'cuisine'),(23,'cycling'),
(24,'whisky'),(24,'hiking'),
(25,'football'),(25,'beaches'),
(26,'onsen'),(26,'tea'),(26,'gardens'),
(27,'hiking'),(27,'national-parks'),
(28,'shopping'),(28,'history'),
(29,'art'),(29,'opera'),
(30,'culture'),(30,'nature');

-- Destination
INSERT INTO Destination (name, country, description, latitude, longitude, popularityScore) VALUES
('Cape Town',       'South Africa', 'Mother City with Table Mountain, beaches and vibrant food scene.',            -33.9249,   18.4241,  95),
('Paris',           'France',       'City of Light renowned for the Eiffel Tower, cuisine and fashion.',           48.8566,    2.3522,   98),
('Bali',            'Indonesia',    'Tropical island paradise with rice terraces, temples and surf beaches.',       -8.3405,  115.0920,  93),
('New York City',   'USA',          'The Big Apple with world-class museums, Broadway and iconic skyline.',         40.7128,  -74.0060,  96),
('Tokyo',           'Japan',        'Ultramodern metropolis blending ancient traditions with cutting-edge tech.',   35.6762,  139.6503,  97),
('Santorini',       'Greece',       'Iconic white-and-blue island with caldera views and volcanic beaches.',        36.3932,   25.4615,  91),
('Machu Picchu',    'Peru',         'Incan citadel set high in the Andes Mountains above the Sacred Valley.',     -13.1631,  -72.5450,  89),
('Kruger Park',     'South Africa', 'World-famous national park home to the Big Five wildlife.',                  -23.9884,   31.5547,  88),
('Maldives',        'Maldives',     'Indian Ocean archipelago of coral atolls with overwater bungalows.',            4.1755,   73.5093,  94),
('Rome',            'Italy',        'Eternal City with the Colosseum, Vatican and incredible piazzas.',            41.9028,   12.4964,  96),
('Sydney',          'Australia',    'Harbour city famed for Opera House, Bondi Beach and cosmopolitan culture.',  -33.8688,  151.2093,  93),
('Marrakech',       'Morocco',      'Imperial city with vibrant souks, riads and the famous Djemaa el-Fna.',       31.6295,   -7.9811,  87),
('Zurich',          'Switzerland',  'Alpine sophistication with pristine lakes and world-class skiing.',           47.3769,    8.5417,  85),
('Bangkok',         'Thailand',     'City of Angels bursting with street food, temples and lively nightlife.',     13.7563,  100.5018,  92),
('Zanzibar',        'Tanzania',     'Spice island with turquoise waters, coral reefs and Swahili culture.',         -6.1659,   39.2026,  84);

-- Flight
INSERT INTO Flight (departureAirport, arrivalAirport, departureDatetime, arrivalDatetime, price, airline, seatsAvailable) VALUES
('OR Tambo International (JNB)',    'Cape Town International (CPT)',  '2026-07-01 06:00:00','2026-07-01 08:10:00',   980.00,'South African Airways', 42),
('Charles de Gaulle (CDG)',          'Cape Town International (CPT)',  '2026-07-05 10:30:00','2026-07-05 23:45:00',  8500.00,'Air France',             18),
('Ngurah Rai International (DPS)',   'Soekarno-Hatta (CGK)',           '2026-07-10 07:00:00','2026-07-10 09:30:00',   320.00,'Garuda Indonesia',       85),
('JFK International (JFK)',          'Los Angeles International (LAX)','2026-07-12 08:00:00','2026-07-12 11:30:00',   350.00,'American Airlines',      60),
('Narita International (NRT)',       'Singapore Changi (SIN)',          '2026-07-14 13:00:00','2026-07-14 19:00:00',  1200.00,'Japan Airlines',         30),
('Athens International (ATH)',       'Santorini National (JTR)',        '2026-07-20 09:00:00','2026-07-20 10:05:00',   280.00,'Aegean Airlines',        50),
('Jorge Chavez International (LIM)', 'Alejandro Velasco (CUZ)',         '2026-08-01 06:30:00','2026-08-01 07:50:00',   420.00,'LATAM Airlines',         40),
('OR Tambo International (JNB)',    'Skukuza Airport (SZK)',           '2026-08-05 07:00:00','2026-08-05 08:30:00',   750.00,'South African Airways',  20),
('Velana International (MLE)',       'Gan International (GAN)',          '2026-08-10 09:00:00','2026-08-10 10:15:00',   500.00,'Maldivian',              25),
('Fiumicino (FCO)',                  'Venice Marco Polo (VCE)',          '2026-08-15 07:30:00','2026-08-15 08:45:00',   220.00,'Alitalia',               70),
('Sydney Kingsford Smith (SYD)',     'Cairns Airport (CNS)',             '2026-08-20 06:00:00','2026-08-20 09:15:00',   480.00,'Qantas',                 55),
('Mohammed V International (CMN)',   'Marrakech Menara (RAK)',           '2026-09-01 11:00:00','2026-09-01 12:05:00',   190.00,'Royal Air Maroc',        90),
('Zurich Airport (ZRH)',             'Geneva Airport (GVA)',             '2026-09-05 07:15:00','2026-09-05 08:05:00',   180.00,'Swiss International',    65),
('Suvarnabhumi (BKK)',               'Phuket International (HKT)',       '2026-09-10 14:00:00','2026-09-10 15:20:00',   260.00,'Thai Airways',           75),
('JKIA Nairobi (NBO)',              'Abeid Amani Karume (ZNZ)',         '2026-09-15 08:30:00','2026-09-15 09:45:00',   380.00,'Kenya Airways',          48),
('Dubai International (DXB)',        'OR Tambo International (JNB)',    '2026-10-01 23:00:00','2026-10-02 07:30:00',  6200.00,'Emirates',               22),
('Heathrow (LHR)',                   'Narita International (NRT)',       '2026-10-05 11:00:00','2026-10-06 07:30:00',  9800.00,'British Airways',        15),
('Charles de Gaulle (CDG)',          'Suvarnabhumi (BKK)',               '2026-10-10 22:00:00','2026-10-11 14:30:00',  7500.00,'Air France',             28),
('Hartsfield-Jackson (ATL)',         'JFK International (JFK)',          '2026-11-01 06:00:00','2026-11-01 09:15:00',   320.00,'Delta Airlines',         80),
('Abu Dhabi International (AUH)',    'Velana International (MLE)',       '2026-11-10 03:00:00','2026-11-10 07:45:00',  4200.00,'Etihad Airways',         35);

-- Accommodation
INSERT INTO Accommodation (name, type, address, pricePerNight, rating) VALUES
('Table Bay Hotel',         'Hotel',   'Quay 6, V&A Waterfront, Cape Town, ZA',           4500.00, 4.8),
('Hotel de Crillon',        'Hotel',   '10 Place de la Concorde, Paris, FR',             12000.00, 4.9),
('COMO Uma Ubud',           'Resort',  'Jalan Raya Sanggingan, Ubud, Bali, ID',           3800.00, 4.7),
('The Standard New York',   'Hotel',   '848 Washington St, New York, US',                 6500.00, 4.3),
('Park Hyatt Tokyo',        'Hotel',   '3-7-1-2 Nishi-Shinjuku, Tokyo, JP',              9000.00, 4.8),
('Mystique Santorini',      'Boutique','Oia, 84702, Santorini, GR',                       8500.00, 4.9),
('Inkaterra Machu Picchu',  'Lodge',   'Aguas Calientes, Machu Picchu, PE',               5200.00, 4.6),
('Lion Sands Game Reserve', 'Lodge',   'Kruger National Park, Limpopo, ZA',               9500.00, 4.9),
('Gili Lankanfushi',        'Resort',  'Lankanfushi Island, North Male, MV',             15000.00, 4.9),
('Hotel Hassler Roma',      'Hotel',   'Piazza Trinita dei Monti 6, Rome, IT',            7800.00, 4.7),
('Quay Hotel Sydney',       'Hotel',   'Overseas Passenger Terminal, Sydney, AU',         5600.00, 4.6),
('La Mamounia',             'Hotel',   'Avenue Bab Jdid, Marrakech, MA',                 11000.00, 4.9),
('The Dolder Grand Zurich', 'Hotel',   'Kurhausstrasse 65, Zurich, CH',                   9200.00, 4.8),
('Capella Bangkok',         'Hotel',   '300/2 Charoenkrung Rd, Bangkok, TH',              8800.00, 4.8),
('Zuri Zanzibar',           'Resort',  'Kendwa Beach, Zanzibar, TZ',                      3500.00, 4.5);

-- AccommodationAmenity
INSERT INTO AccommodationAmenity (accommodationID, amenity) VALUES
(1,'Pool'),(1,'Spa'),(1,'Restaurant'),(1,'WiFi'),(1,'Concierge'),
(2,'Michelin Restaurant'),(2,'Spa'),(2,'Butler Service'),(2,'WiFi'),
(3,'Infinity Pool'),(3,'Yoga'),(3,'Spa'),(3,'Organic Restaurant'),(3,'WiFi'),
(4,'Rooftop Bar'),(4,'Gym'),(4,'WiFi'),(4,'Restaurant'),
(5,'Pool'),(5,'Spa'),(5,'Multiple Restaurants'),(5,'WiFi'),(5,'Concierge'),
(6,'Infinity Pool'),(6,'Caldera Views'),(6,'Spa'),(6,'WiFi'),
(7,'Garden'),(7,'Restaurant'),(7,'WiFi'),(7,'Nature Walks'),
(8,'Bush Walks'),(8,'Game Drives'),(8,'Pool'),(8,'All Inclusive'),
(9,'Overwater Bungalows'),(9,'Diving'),(9,'Spa'),(9,'WiFi'),(9,'All Inclusive'),
(10,'Rooftop Restaurant'),(10,'Spa'),(10,'WiFi'),(10,'Concierge'),
(11,'Harbour Views'),(11,'Restaurant'),(11,'Bar'),(11,'WiFi'),(11,'Gym'),
(12,'Pool'),(12,'Spa'),(12,'Casino'),(12,'Gardens'),(12,'WiFi'),
(13,'Spa'),(13,'Pool'),(13,'Golf'),(13,'Fine Dining'),(13,'WiFi'),
(14,'River Views'),(14,'Infinity Pool'),(14,'Spa'),(14,'Restaurants'),(14,'WiFi'),
(15,'Beach'),(15,'Pool'),(15,'Diving'),(15,'Restaurant'),(15,'WiFi');

-- TouristAttraction
INSERT INTO TouristAttraction (name, description, category, ticketPrice) VALUES
('Table Mountain Aerial Cableway', 'Iconic rotating cable car to the flat-topped Table Mountain summit.',          'Nature',     360.00),
('Eiffel Tower',                   'Iron lattice tower on the Champ de Mars, symbol of Paris.',                   'Landmark',   260.00),
('Tanah Lot Temple',               'Ancient Hindu shrine perched on a rock formation in the sea.',                 'Cultural',    60.00),
('Statue of Liberty',              'Colossal neoclassical sculpture on Liberty Island, New York.',                 'Landmark',   240.00),
('Senso-ji Temple',                'Ancient Buddhist temple in Asakusa, Tokyo oldest temple in the city.',        'Cultural',     0.00),
('Akrotiri Archaeological Site',   'Prehistoric settlement buried by the Minoan eruption on Santorini.',           'Historical', 140.00),
('Machu Picchu Citadel',           'The Lost City of the Incas UNESCO World Heritage site in the Andes.',         'Historical', 700.00),
('Kruger National Park Safari',    'Self-drive or guided safari through Africa premier wildlife reserve.',         'Wildlife',   450.00),
('Manta Ray Night Snorkel',        'Night snorkelling with majestic manta rays in Maldivian waters.',             'Adventure', 1200.00),
('Colosseum Rome',                 'Iconic elliptical amphitheatre at the heart of ancient Rome.',                 'Historical', 180.00),
('Sydney Opera House Tour',        'Guided behind-the-scenes tour of the UNESCO World Heritage venue.',           'Culture',    450.00),
('Djemaa el-Fna Market',           'Buzzing central square with storytellers, musicians and food stalls.',        'Cultural',     0.00),
('Rhine Falls Boat Trip',          'Close-up boat excursion to the largest waterfall in Europe.',                 'Nature',     350.00),
('Grand Palace Bangkok',           'Opulent royal complex and home of the Emerald Buddha.',                       'Historical', 500.00),
('Prison Island Zanzibar',         'Island with giant tortoises and ruins of a former British prison.',           'Historical', 250.00),
('Robben Island Tour',             'UNESCO site where Nelson Mandela was imprisoned for 18 years.',               'Historical', 450.00),
('Louvre Museum',                  'World largest art museum housing the Mona Lisa and Venus de Milo.',           'Culture',    170.00),
('Ubud Monkey Forest',             'Sacred sanctuary home to over 700 Balinese long-tailed macaques.',            'Nature',     100.00),
('Times Square',                   'Iconic commercial intersection known as the Crossroads of the World.',        'Landmark',     0.00),
('Fushimi Inari Taisha',           'Shinto shrine famous for its thousands of vermilion torii gates.',            'Cultural',     0.00);

-- Restaurant
INSERT INTO Restaurant (destinationID, name, cuisineType, address, rating, priceRange) VALUES
(1,  'The Test Kitchen',     'Modern African',  '375 Albert Rd, Woodstock, Cape Town',       4.9, '$$$$'),
(1,  'La Colombe',           'French Fusion',   'Silvermist Mountain Lodge, Cape Town',       4.8, '$$$$'),
(2,  'Le Jules Verne',       'French',          'Eiffel Tower, Champ de Mars, Paris',         4.7, '$$$$'),
(2,  'L Ambroisie',          'French',          '9 Place des Vosges, Paris',                  4.9, '$$$$'),
(3,  'Locavore',             'Indonesian',      'Jalan Dewi Sita 10, Ubud, Bali',             4.8, '$$$'),
(3,  'Mozaic Restaurant',    'French-Asian',    'Jalan Raya Sanggingan, Ubud, Bali',          4.7, '$$$$'),
(4,  'Eleven Madison Park',  'Contemporary',    '11 Madison Ave, New York City',              4.8, '$$$$'),
(4,  'Katz Delicatessen',    'American Deli',   '205 E Houston St, New York City',            4.5, '$$'),
(5,  'Sukiyabashi Jiro',     'Sushi',           'Tsukamoto Sogyo Bldg, Chuo-ku, Tokyo',      4.9, '$$$$'),
(5,  'Ichiran Ramen',        'Japanese',        '1-22-7 Jinnan, Shibuya, Tokyo',              4.6, '$$'),
(6,  'Ambrosia Santorini',   'Mediterranean',   'Oia, Santorini',                             4.7, '$$$'),
(6,  'Metaxy Mas',           'Greek',           'Exo Gialos, Santorini',                      4.6, '$$$'),
(7,  'Indio Feliz',          'French-Peruvian', 'Av Pachacutec 103, Aguas Calientes',         4.5, '$$$'),
(12, 'Jemaa Food Stalls',    'Moroccan',        'Djemaa el-Fna Square, Marrakech',            4.4, '$'),
(12, 'Al Fassia',            'Moroccan',        'Avenue Mohammed V, Marrakech',               4.7, '$$$'),
(11, 'Aria Restaurant',      'Modern Australian','1 Macquarie St, Sydney',                    4.7, '$$$$'),
(11, 'The Boathouse',        'Seafood',         'Palm Beach, Sydney',                         4.5, '$$$'),
(14, 'Bo.lan',               'Thai',            '24 Sukhumvit 53, Bangkok',                   4.7, '$$$'),
(14, 'Gaggan Anand',         'Progressive Indian','68/1 Soi Langsuan, Bangkok',               4.9, '$$$$'),
(15, 'The Rock Restaurant',  'Seafood',         'Michamvi Pingwe, Zanzibar',                  4.6, '$$$');

-- TravelPackage (agencyUserID = userID of agency)
INSERT INTO TravelPackage (agencyUserID, title, description, basePrice, durationDays, itinerary, status, startDate, endDate, dateCreated) VALUES
(1,  'Cape Town and Winelands Escape',    'Explore the Mother City, Table Mountain and the world-class Cape Winelands.',          18500.00,  7, 'Day 1: Arrive CPT. Day 2-3: Table Mountain and City Bowl. Day 4-5: Cape Winelands. Day 6: Cape Point. Day 7: Depart.',           'active','2026-07-01','2026-07-07','2026-01-15'),
(1,  'Garden Route Adventure',            'Drive South Africa most scenic coastal route from Mossel Bay to Storms River.',        22000.00, 10, 'Day 1: JNB-CPT. Day 2: Mossel Bay. Day 3: Wilderness. Day 4: Knysna. Day 5-6: Tsitsikamma. Day 7-9: Port Elizabeth.',          'active','2026-08-01','2026-08-10','2026-01-20'),
(2,  'Europe on a Shoestring',            'Hit 5 European capitals in 14 days without breaking the bank.',                        32000.00, 14, 'London-Paris-Amsterdam-Berlin-Prague. 2-3 nights per city, budget hostels, rail passes included.',                              'active','2026-09-01','2026-09-14','2026-02-01'),
(3,  'Greek Island Hopper',               'Santorini, Mykonos and Rhodes - sun, sea and ancient history.',                        41500.00, 10, 'Day 1-4: Santorini. Day 5-7: Mykonos. Day 8-10: Rhodes.',                                                                      'active','2026-07-20','2026-07-29','2026-02-10'),
(3,  'Romantic Paris Weekend',            'A curated 4-night Paris getaway perfect for couples.',                                 28000.00,  4, 'Day 1: Arrive CDG, Seine cruise. Day 2: Louvre, Eiffel Tower. Day 3: Versailles. Day 4: Depart.',                               'active','2026-08-15','2026-08-18','2026-02-14'),
(4,  'Inca Trail and Machu Picchu Trek',  'Four-day classic Inca Trail ending at the Sun Gate of Machu Picchu at sunrise.',      55000.00,  8, 'Day 1: Cusco acclimatisation. Day 2-5: Inca Trail trekking. Day 6: Machu Picchu. Day 7: Cusco. Day 8: Fly Lima.',               'active','2026-08-01','2026-08-08','2026-02-20'),
(5,  'Bali Serenity Retreat',             'Wellness and culture in the Island of the Gods - yoga, cooking and temple visits.',    29500.00,  8, 'Day 1: Arrive DPS. Day 2-3: Ubud rice terraces and temples. Day 4-5: Yoga retreat. Day 6-7: Seminyak beach. Day 8: Depart.',  'active','2026-07-10','2026-07-17','2026-03-01'),
(5,  'Maldives Overwater Luxury',         'Five nights in an overwater bungalow with snorkelling, diving and sunset cruises.',   95000.00,  6, 'Day 1: Arrive MLE, speedboat transfer. Day 2-5: Diving, snorkelling, spa. Day 6: Depart.',                                       'active','2026-08-10','2026-08-15','2026-03-05'),
(6,  'Paris and French Riviera Luxury',   'The ultimate French luxury experience from Paris to Nice and Monaco.',                 88000.00, 10, 'Day 1-4: Paris. Day 5: TGV to Nice. Day 6-8: French Riviera. Day 9: Monaco. Day 10: Depart.',                                  'active','2026-09-05','2026-09-14','2026-03-10'),
(8,  'Bangkok and Thai Islands',          'Bangkok temples and street food followed by crystal-clear island paradise.',           35000.00, 10, 'Day 1-3: Bangkok temples and markets. Day 4: Fly to Koh Samui. Day 5-9: Island hopping. Day 10: Depart.',                      'active','2026-09-10','2026-09-19','2026-03-20'),
(9,  'Swiss Alps Ski Safari',             'A week of world-class skiing across Zermatt, Verbier and St. Moritz.',                72000.00,  7, 'Day 1: Arrive ZRH. Day 2-3: Zermatt. Day 4-5: Verbier. Day 6-7: St. Moritz.',                                                  'active','2026-12-20','2026-12-26','2026-04-01'),
(10, 'Big Five Kruger Safari',            'Immersive 5-day safari in the Kruger with expert trackers and all-inclusive lodge.',  48000.00,  5, 'Day 1: Fly JNB-SZK. Day 2-4: Morning and evening game drives. Day 5: Fly back.',                                               'active','2026-08-05','2026-08-09','2026-04-05'),
(10, 'Cape Town and Kruger Combo',        'The best of South Africa - vibrant Cape Town and the wild Kruger Park.',              62000.00, 12, 'Day 1-5: Cape Town. Day 6: Fly to Kruger. Day 7-11: Safari. Day 12: Fly home.',                                                 'active','2026-09-01','2026-09-12','2026-04-10'),
(4,  'Kilimanjaro Group Summit',          'Join a guided group climb up Africa highest peak through 5 climate zones.',           68000.00, 10, 'Day 1: Arrive Arusha. Day 2-8: Ascent via Machame route. Day 9: Descent and celebration. Day 10: Fly.',                        'active','2026-08-15','2026-08-24','2026-04-15'),
(2,  'Tokyo and Kyoto Group Cultural Tour','Explore Japan ancient temples and futuristic cities with fellow travel enthusiasts.',55000.00,  9, 'Day 1-4: Tokyo. Day 5: Shinkansen to Kyoto. Day 6-8: Kyoto temples. Day 9: Fly.',                                               'active','2026-10-05','2026-10-13','2026-04-20');

-- RegularPackage
INSERT INTO RegularPackage (packageID) VALUES (1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12),(13);

-- GroupTrip
INSERT INTO GroupTrip (packageID, minGroupSize, maxGroupSize, currentGroupSize, groupDeadline, groupDescription) VALUES
(14, 6, 18,  9, '2026-08-01', 'Join fellow adventurers for a guided Kilimanjaro summit. Training plan provided.'),
(15, 4, 20, 12, '2026-09-25', 'A cultural group immersion into Japan. Guided by a Japanese-speaking expert.');

-- PackageDestination
INSERT INTO PackageDestination (packageID, destinationID) VALUES
(1,1),(2,1),(3,2),(3,10),(3,13),(4,6),(5,2),(6,7),(7,3),(8,9),(9,2),(10,14),(11,13),(12,8),(13,1),(13,8),(14,15),(15,5);

-- PackageFlight
INSERT INTO PackageFlight (packageID, flightID) VALUES
(1,1),(1,2),(2,1),(3,2),(4,6),(5,2),(6,7),(7,3),(8,9),(8,20),(9,2),(10,14),(11,13),(12,8),(13,1),(13,8),(14,15),(15,17);

-- PackageAccommodation
INSERT INTO PackageAccommodation (packageID, accommodationID, staysAt) VALUES
(1,1,6),(2,1,9),(3,2,3),(4,6,9),(5,2,3),(6,7,6),(7,3,7),(8,9,5),(9,2,4),(9,12,5),(10,14,8),(11,13,6),(12,8,4),(13,1,4),(13,8,7),(14,15,8),(15,5,8);

-- PackageAttraction
INSERT INTO PackageAttraction (packageID, attractionID) VALUES
(1,1),(1,16),(2,1),(3,17),(4,6),(5,2),(5,17),(6,7),(7,3),(7,18),(8,9),(9,2),(9,17),(10,14),(11,13),(12,8),(13,1),(13,8),(15,5),(15,20);

-- Booking (travellerUserID = userID of traveller, i.e. 11-30)
INSERT INTO Booking (travellerUserID, packageID, bookingDate, numTravellers, totalAmount, status, paymentStatus) VALUES
(11, 1, '2026-03-10', 2,  37000.00,'confirmed','paid'),
(12, 6, '2026-03-15', 1,  55000.00,'confirmed','paid'),
(13, 5, '2026-03-20', 2,  56000.00,'confirmed','paid'),
(14, 6, '2026-03-25', 1,  55000.00,'confirmed','paid'),
(15,15, '2026-03-28', 1,  55000.00,'confirmed','paid'),
(16,10, '2026-04-01', 2,  70000.00,'confirmed','paid'),
(17,12, '2026-04-05', 1,  48000.00,'confirmed','paid'),
(18, 8, '2026-04-08', 2, 190000.00,'confirmed','paid'),
(19, 2, '2026-04-10', 1,  22000.00,'confirmed','paid'),
(20, 3, '2026-04-12', 1,  32000.00,'confirmed','paid'),
(21, 7, '2026-04-15', 1,  29500.00,'confirmed','paid'),
(22,14, '2026-04-18', 1,  68000.00,'pending',  'unpaid'),
(23, 9, '2026-04-20', 2, 176000.00,'confirmed','paid'),
(24, 4, '2026-04-22', 2,  83000.00,'confirmed','paid'),
(25, 1, '2026-04-25', 1,  18500.00,'confirmed','paid'),
(26, 7, '2026-04-28', 2,  59000.00,'confirmed','paid'),
(27,12, '2026-05-01', 2,  96000.00,'confirmed','paid'),
(28,10, '2026-05-03', 1,  35000.00,'pending',  'unpaid'),
(29, 9, '2026-05-05', 1,  88000.00,'confirmed','paid'),
(30,13, '2026-05-07', 2, 124000.00,'confirmed','paid'),
(11, 8, '2026-05-08', 1,  95000.00,'pending',  'unpaid'),
(13,11, '2026-05-09', 2, 144000.00,'confirmed','paid'),
(15, 4, '2026-05-10', 1,  41500.00,'confirmed','paid'),
(17,15, '2026-05-11', 1,  55000.00,'cancelled','refunded'),
(19, 6, '2026-05-12', 1,  55000.00,'confirmed','paid');

-- Review (travellerUserID = userID of traveller)
INSERT INTO Review (travellerUserID, rating, comment, reviewDate, targetType, packageID, agencyUserID) VALUES
(11, 4.8,'Absolutely stunning Cape Town experience. The winelands tour was a highlight!',                          '2026-07-09','package',1,  NULL),
(12, 4.9,'The Inca Trail was life-changing. Our guide was exceptional and the sunrise at Sun Gate was magical.',   '2026-08-10','package',6,  NULL),
(13, 4.7,'Paris was dreamy. Hotel de Crillon exceeded expectations. Would return in a heartbeat.',                 '2026-08-20','package',5,  NULL),
(14, 4.6,'Great trek but be prepared - it is physically demanding. The agency support was excellent.',             '2026-08-10','package',6,  NULL),
(15, 4.8,'Japan group tour was perfectly organised. Loved meeting fellow travellers with the same passion.',       '2026-10-15','package',15, NULL),
(16, 4.5,'Bangkok street food was unreal! Koh Samui beach days were the perfect contrast.',                       '2026-09-21','package',10, NULL),
(17, 4.9,'Lion Sands was incredible. Saw all the Big Five in just 3 days. Totally worth every cent.',             '2026-08-11','package',12, NULL),
(18, 5.0,'The Maldives overwater bungalow was a dream. Best holiday of my life, no question.',                    '2026-08-17','package',8,  NULL),
(19, 4.4,'Great scenic drive but the itinerary felt slightly rushed at some stops.',                               '2026-08-12','package',2,  NULL),
(20, 4.3,'Good value for 5 cities in 2 weeks. Accommodation varies but overall a solid trip.',                    '2026-09-16','package',3,  NULL),
(21, 4.7,'Ubud yoga retreat was exactly what I needed. Peaceful, spiritual and rejuvenating.',                    '2026-07-19','package',7,  NULL),
(23, 4.9,'Velvet Journeys went above and beyond. Paris to Monaco in style - unforgettable.',                      '2026-09-16','package',9,  NULL),
(24, 4.8,'Santorini was as beautiful as the photos. Mystique hotel has the most incredible infinity pool.',        '2026-07-31','package',4,  NULL),
(25, 4.6,'Lovely Cape Town package. Table Mountain hike was breathtaking. Winelands tasting was superb.',         '2026-07-09','package',1,  NULL),
(26, 4.5,'Bali was amazing. Loved the Monkey Forest and cooking class.',                                          '2026-07-19','package',7,  NULL),
(27, 4.8,'Kruger safari far exceeded expectations. Incredible wildlife encounters every single day.',              '2026-08-11','package',12, NULL),
(29, 4.7,'French Riviera is absolutely stunning. Velvet Journeys curated a flawless luxury experience.',          '2026-09-16','package',9,  NULL),
(30, 4.6,'South Africa combo is brilliant - Cape Town vibrancy then Kruger wildlife. Highly recommend.',          '2026-09-14','package',13, NULL),
-- Agency reviews (agencyUserID = userID of agency, i.e. 1-10)
(11, 4.8,'SunWay Travels is professional, responsive and truly passionate about South African tourism.',           '2026-07-10','agency', NULL, 1),
(12, 4.9,'Horizon Adventures staff are knowledgeable, safety-conscious and incredibly supportive on the trail.',  '2026-08-12','agency', NULL, 4),
(13, 4.7,'Azure Escapes made our Paris trip effortless. Every detail was perfectly arranged.',                    '2026-08-21','agency', NULL, 3),
(15, 4.6,'Globe Trekkers created a wonderful group atmosphere. Great for solo travellers too.',                   '2026-10-16','agency', NULL, 2),
(16, 4.5,'Coral Coast Tours knows Southeast Asia inside out. Excellent restaurant recommendations.',              '2026-09-22','agency', NULL, 8),
(17, 4.9,'Savanna Trails is simply the best safari operator in South Africa. Expert guides, top lodges.',         '2026-08-12','agency', NULL,10),
(18, 5.0,'Pearl Voyages delivered pure perfection. The Maldives package was worth every cent.',                   '2026-08-18','agency', NULL, 5),
(19, 4.3,'Good agency overall but communication could be slightly faster before the trip.',                       '2026-08-13','agency', NULL, 1),
(21, 4.8,'Pearl Voyages Bali package had brilliant local guides who truly enhanced the experience.',              '2026-07-20','agency', NULL, 5),
(23, 4.9,'Velvet Journeys redefined what luxury travel means. Every moment felt curated and special.',            '2026-09-17','agency', NULL, 6),
(24, 4.8,'Azure Escapes Santorini package was flawless from booking to departure. Will use again.',              '2026-08-01','agency', NULL, 3),
(27, 4.7,'Savanna Trails staff are passionate and professional. The Kruger experience was world-class.',          '2026-08-12','agency', NULL,10);

-- GroupTripMember (travellerUserID = userID of traveller)
INSERT INTO GroupTripMember (packageID, travellerUserID, joinedDate) VALUES
(14,12,'2026-04-16'),(15,15,'2026-03-28'),(14,16,'2026-04-19'),(15,17,'2026-05-11'),
(14,19,'2026-04-25'),(15,21,'2026-04-18'),(14,22,'2026-04-20'),(15,24,'2026-04-22'),
(14,27,'2026-05-02'),(15,28,'2026-05-04'),(15,11,'2026-05-08'),(14,13,'2026-05-09');

-- AgencyManagesGroupTripMember
INSERT INTO AgencyManagesGroupTripMember (packageID, travellerUserID, agencyUserID) VALUES
(14,12,4),(14,16,4),(14,19,4),(14,22,4),(14,27,4),(14,13,4),
(15,15,2),(15,17,2),(15,21,2),(15,24,2),(15,28,2),(15,11,2);

SET FOREIGN_KEY_CHECKS = 1;
