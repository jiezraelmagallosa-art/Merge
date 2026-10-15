import os

import mysql.connector
from dotenv import load_dotenv

dotenv_path = os.path.join(os.path.dirname(__file__), ".env")
load_dotenv(dotenv_path, override=True)


def get_db_connection():
    load_dotenv(dotenv_path, override=True)
    db_name = os.getenv("DB_NAME", "cit_attendance_db")
    if db_name == "sbc_internship_db":
        db_name = "cit_attendance_db"

    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        database=db_name,
        port=int(os.getenv("DB_PORT", 3306)),
    )