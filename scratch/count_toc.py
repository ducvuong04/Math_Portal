import mysql.connector
from bs4 import BeautifulSoup

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="math_portal_12"
)
cursor = db.cursor(buffered=True)

cursor.execute("SELECT id, title, theory FROM topics")
rows = cursor.fetchall()

vj_toc_count = 0
for row in rows:
    topic_id, title, theory_html = row
    if not theory_html:
        continue
    soup = BeautifulSoup(theory_html, 'html.parser')
    if soup.find(class_='vj-toc'):
        vj_toc_count += 1

print(f"Number of topics with vj-toc class: {vj_toc_count}")
