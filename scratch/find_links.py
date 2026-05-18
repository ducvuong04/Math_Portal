import mysql.connector
from bs4 import BeautifulSoup
import sys
import os

# Set stdout to UTF-8
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

total_links = 0
relative_links = []
external_links = {}

for row in rows:
    topic_id, title, theory_html = row
    if not theory_html:
        continue
    
    soup = BeautifulSoup(theory_html, 'html.parser')
    for a in soup.find_all('a'):
        total_links += 1
        href = a.get('href', '').strip()
        text = a.get_text(strip=True)
        if href.startswith('..') or '.jsp' in href or href.startswith('/') or (not href.startswith('http') and not href.startswith('#')):
            relative_links.append((topic_id, title, text, href))
        else:
            # Parse domain
            domain = "unknown"
            if href.startswith('http'):
                parts = href.split('/')
                if len(parts) > 2:
                    domain = parts[2]
            external_links[domain] = external_links.get(domain, 0) + 1

print(f"Total links: {total_links}")
print(f"Relative/JSP links: {len(relative_links)}")
print(f"External domains and counts:")
for domain, count in sorted(external_links.items(), key=lambda x: x[1], reverse=True):
    print(f"  - {domain}: {count}")

# Write first 100 relative links to a text file for inspection
with open(os.path.join(os.path.dirname(__file__), 'links_report.txt'), 'w', encoding='utf-8') as f:
    f.write(f"Total Relative Links: {len(relative_links)}\n\n")
    for idx, (tid, title, text, href) in enumerate(relative_links):
        f.write(f"{idx+1}. Topic ID: {tid} | Topic Title: {title}\n")
        f.write(f"   Text: \"{text}\"\n")
        f.write(f"   Href: \"{href}\"\n\n")

print("Report written to scratch/links_report.txt successfully!")
