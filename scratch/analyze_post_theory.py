import mysql.connector
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="math_portal_12"
)
cursor = db.cursor(buffered=True)

cursor.execute("SELECT id, title, theory FROM topics")
rows = cursor.fetchall()

end_headers = []
for row in rows:
    topic_id, title, theory_html = row
    if not theory_html:
        continue
    soup = BeautifulSoup(theory_html, 'html.parser')
    
    # Let's find all h3 or h4 or h2 elements near the end
    headers = soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])
    for h in headers:
        text = h.get_text(strip=True).lower()
        if 'học tốt' in text or 'xem thêm' in text or 'tài liệu học tốt' in text or 'bài giảng' in text or 'chuyên đề' in text or 'đề thi' in text:
            end_headers.append((topic_id, title, h.get_text(strip=True)))

print(f"Total matching end headers found: {len(end_headers)}")
print("Sample end headers:")
for idx, (tid, title, h_text) in enumerate(end_headers[:30]):
    print(f"Topic ID {tid} - {title}: \"{h_text}\"")
