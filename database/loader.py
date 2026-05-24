'''
To the teamates: r
Run this python script to populate your local version of the database
with data.
There are packages you need to install, namely python and faker, the. instructions are in the README.
'''

import mariadb
import sys
from faker import Faker
import random
from datetime import date, timedelta

fake = Faker()

DB_CONFIG = { # this section can be modified to suit whatever DBMS you use, i.e. mysql
    'host': '127.0.0.1',
    'port': 8889,
    'user': 'root', 
    'password': 'root', # use your password
    'database': 'tripistry',
    'autocommit': False
}

def create_connection():
    try:
        conn = mariadb.connect(**DB_CONFIG)
        return conn
    except mariadb.Error as e:
        print(f"Error: {e}")
        sys.exit(1)

# --- HELPER GENERATORS ---

def load_users(cursor, count, role):
    user_ids = []
    for _ in range(count):
        email = fake.unique.email() if role == 'traveller' else fake.unique.company_email()
        cursor.execute("INSERT INTO User (email, passwordHash, phone, role) VALUES (?, 'hash', ?, ?)", 
                       (email, fake.phone_number()[:25], role))
        user_ids.append(cursor.lastrowid)
    return user_ids

def load_travellers(cursor, user_ids):
    for uid in user_ids:
        cursor.execute("""
            INSERT INTO Traveller (userID, firstName, lastName, passportNum, nationality, DOB)
            VALUES (?, ?, ?, ?, ?, ?)
        """, (uid, fake.first_name()[:60], fake.last_name()[:60], fake.unique.bothify('??######'), fake.country()[:80], fake.date_of_birth(minimum_age=18)))
        # Add 2 preferences per traveller
        for _ in range(2):
            cursor.execute("INSERT IGNORE INTO TravellerPreference (userID, preference) VALUES (?, ?)", 
                           (uid, random.choice(['beach', 'safari', 'hiking', 'food', 'art'])))

def load_agencies(cursor, user_ids):
    for uid in user_ids:
        cursor.execute("""
            INSERT INTO TravelAgency (userID, name, description, website, rating, address)
            VALUES (?, ?, ?, ?, ?, ?)
        """, (uid, fake.company()[:120], fake.catch_phrase(), fake.url()[:200], round(random.uniform(3, 5), 2), fake.address()[:300]))

def load_destinations(cursor, count):
    dest_ids = []
    for _ in range(count):
        cursor.execute("INSERT INTO Destination (name, country, description, popularityScore) VALUES (?, ?, ?, ?)",
                       (fake.city()[:120], fake.country()[:80], fake.text(max_nb_chars=200), random.randint(50, 100)))
        dest_ids.append(cursor.lastrowid)
    return dest_ids

def load_content(cursor, dest_ids):
    # Load Accommodations
    acc_ids = []
    for _ in range(40):
        cursor.execute("INSERT INTO Accommodation (name, type, pricePerNight, rating) VALUES (?, ?, ?, ?)",
                       (fake.company() + " Hotel", random.choice(['Resort', 'Hotel', 'Lodge']), random.uniform(500, 5000), random.uniform(3, 5)))
        acc_ids.append(cursor.lastrowid)
    
    # Load Restaurants
    for _ in range(40):
        cursor.execute("INSERT INTO Restaurant (destinationID, name, cuisineType, priceRange) VALUES (?, ?, ?, ?)",
                       (random.choice(dest_ids), fake.company() + " Eats", random.choice(['Italian', 'Thai', 'Local']), random.choice(['$', '$$', '$$$'])))
    return acc_ids

def load_packages(cursor, agency_ids, count):
    pkg_ids = []
    for _ in range(count):
        cursor.execute("""
            INSERT INTO TravelPackage (agencyUserID, title, description, basePrice, durationDays, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        """, (random.choice(agency_ids), fake.catch_phrase()[:200], fake.text(), random.uniform(5000, 30000), random.randint(3, 14)))
        pid = cursor.lastrowid
        pkg_ids.append(pid)
        # Randomly assign as Regular or Group
        if random.random() > 0.2:
            cursor.execute("INSERT INTO RegularPackage (packageID) VALUES (?)", (pid,))
        else:
            cursor.execute("INSERT INTO GroupTrip (packageID, minGroupSize, maxGroupSize) VALUES (?, 4, 20)", (pid,))
    return pkg_ids

def main():
    conn = create_connection()
    cursor = conn.cursor()
    try:
        print("Populating 500+ entries...")
        
        # 1. Users & Subtypes (120 rows)
        t_uids = load_users(cursor, 80, 'traveller')
        a_uids = load_users(cursor, 20, 'agency')
        load_travellers(cursor, t_uids)
        load_agencies(cursor, a_uids)
        
        # 2. Travel Content (150+ rows)
        d_ids = load_destinations(cursor, 30)
        acc_ids = load_content(cursor, d_ids)
        
        # 3. Packages (50 rows)
        p_ids = load_packages(cursor, a_uids, 50)
        
        # 4. Junctions (Linking packages to content - ~150 rows)
        for pid in p_ids:
            cursor.execute("INSERT INTO PackageDestination (packageID, destinationID) VALUES (?, ?)", (pid, random.choice(d_ids)))
            cursor.execute("INSERT INTO PackageAccommodation (packageID, accommodationID) VALUES (?, ?)", (pid, random.choice(acc_ids)))

        # 5. Bookings & Reviews (150 rows)
        for _ in range(75):
            t_id = random.choice(t_uids)
            p_id = random.choice(p_ids)
            cursor.execute("INSERT INTO Booking (travellerUserID, packageID, totalAmount, status) VALUES (?, ?, ?, 'confirmed')",
                           (t_id, p_id, random.uniform(5000, 40000)))
            cursor.execute("INSERT INTO Review (travellerUserID, packageID, rating, targetType) VALUES (?, ?, ?, 'package')",
                           (t_id, p_id, random.uniform(3, 5)))

        conn.commit()
        print("Success! ~650 valid entries inserted.")
    except mariadb.Error as e:
        print(f"Error: {e}")
        conn.rollback()
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()