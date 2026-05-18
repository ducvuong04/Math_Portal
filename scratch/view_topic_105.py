import mysql.connector
import sys

sys.stdout.reconfigure(encoding='utf-8')

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="math_portal_12"
)
cursor = db.cursor(buffered=True)

cursor.execute("SELECT theory FROM topics WHERE id = 105")
theory = cursor.fetchone()[0]

print("THEORY HTML FOR TOPIC 105:")
print(theory)
