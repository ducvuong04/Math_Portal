import requests
from bs4 import BeautifulSoup
import json
import os
import re
from urllib.parse import urlparse, urljoin
import mysql.connector

# Database connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="math_portal_12"
)
cursor = db.cursor(buffered=True)

urls = {
    10: 'https://vietjack.com/toan-10-kn/ly-thuyet-toan-lop-10-ket-noi.jsp',
    11: 'https://vietjack.com/toan-11-kn/ly-thuyet-toan-lop-11-ket-noi.jsp',
    12: 'https://vietjack.com/toan-12-kn/ly-thuyet-toan-lop-12-ket-noi.jsp'
}

# Image dir
img_dir = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'assets', 'images', 'theory')
os.makedirs(img_dir, exist_ok=True)

def download_image(img_url):
    try:
        # Ignore data:image
        if img_url.startswith('data:'):
            return img_url
        filename = os.path.basename(urlparse(img_url).path)
        if not filename:
            filename = 'image.jpg'
        filepath = os.path.join(img_dir, filename)
        if not os.path.exists(filepath):
            res = requests.get(img_url, timeout=10)
            if res.status_code == 200:
                with open(filepath, 'wb') as f:
                    f.write(res.content)
        return '/math_portal_12/assets/images/theory/' + filename
    except Exception as e:
        print(f"Error downloading image {img_url}: {e}")
        return img_url

for grade, index_url in urls.items():
    print(f"Processing grade {grade}...")
    res = requests.get(index_url)
    res.encoding = 'utf-8'
    soup = BeautifulSoup(res.text, 'html.parser')
    
    middle_col = soup.find('div', class_='middle-col')
    if not middle_col:
        continue
        
    current_chapter_name = ""
    current_chapter_id = None
    
    # Iterate all elements inside middle-col sequentially
    for el in middle_col.descendants:
        if el.name == 'b' or el.name == 'h3':
            text = el.get_text(strip=True)
            if 'Chương' in text:
                current_chapter_name = text.replace('Lý thuyết ', '')
                # Insert chapter into DB if not exists
                chapter_key = f"g{grade}_" + "".join([c for c in current_chapter_name if c.isalnum()])[:20].lower()
                cursor.execute("SELECT id FROM chapters WHERE chapter_key = %s", (chapter_key,))
                row = cursor.fetchone()
                if row:
                    current_chapter_id = row[0]
                else:
                    try:
                        cursor.execute("INSERT INTO chapters (chapter_key, title, icon, grade) VALUES (%s, %s, %s, %s)",
                                       (chapter_key, current_chapter_name, 'book', grade))
                        db.commit()
                        current_chapter_id = cursor.lastrowid
                    except mysql.connector.errors.IntegrityError as err:
                        if err.errno == 1062: # Duplicate entry
                            cursor.execute("SELECT id FROM chapters WHERE chapter_key = %s", (chapter_key,))
                            row = cursor.fetchone()
                            if row:
                                current_chapter_id = row[0]
                            else:
                                raise err
                        else:
                            raise err
        elif el.name == 'a':
            href = el.get('href')
            title = el.get_text(strip=True)
            if href and 'ly-thuyet' in href and ('Bài' in title or 'Tổng hợp' in title):
                if current_chapter_id is None:
                    continue
                # It's a theory link
                topic_link = urljoin(index_url, href)
                # Check if this topic already exists (using title and chapter_id)
                cursor.execute("SELECT id FROM topics WHERE chapter_id = %s AND title = %s", (current_chapter_id, title))
                if cursor.fetchone():
                    continue
                
                try:
                    topic_res = requests.get(topic_link, timeout=15)
                    topic_res.encoding = 'utf-8'
                    topic_soup = BeautifulSoup(topic_res.text, 'html.parser')
                    topic_content = topic_soup.find('div', class_='middle-col')
                    
                    if topic_content:
                        # Clean up
                        for ad in topic_content.find_all('div', class_=re.compile('quang-cao|QC|footer|list')):
                            ad.decompose()
                        for script in topic_content.find_all('script'):
                            script.decompose()
                        
                        # Process images
                        for img in topic_content.find_all('img'):
                            src = img.get('src')
                            if src:
                                abs_src = urljoin(topic_link, src)
                                new_src = download_image(abs_src)
                                img['src'] = new_src
                                
                        html_content = str(topic_content)
                        
                        topic_id_str = "topic_" + "".join([c for c in title if c.isalnum()])[:20].lower()
                        cursor.execute("INSERT INTO topics (chapter_id, topic_id_str, title, description, theory) VALUES (%s, %s, %s, %s, %s)",
                                       (current_chapter_id, topic_id_str, title, title, html_content))
                        db.commit()
                        print(f"  Downloaded a topic for chapter {current_chapter_id}")
                except Exception as e:
                    print(f"  Failed a topic: {e}")

print("All done!")
