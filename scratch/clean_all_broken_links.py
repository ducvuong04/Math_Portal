import mysql.connector
from bs4 import BeautifulSoup
import sys
import os

sys.stdout.reconfigure(encoding='utf-8')

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="math_portal_12"
)
cursor = db.cursor(buffered=True)

def clean_theory_html(theory_html):
    if not theory_html:
        return theory_html
        
    soup = BeautifulSoup(theory_html, 'html.parser')
    
    # 1. Decompose vj-toc and vj-more elements
    for toc in soup.find_all(class_=['vj-toc', 'vj-more']):
        toc.decompose()
        
    # 2. Find and decompose post-theory end markers and all their following siblings
    end_markers = [
        'học tốt',
        'xem thêm',
        'các sản phẩm khác',
        'tài liệu clc',
        'giải bài tập lớp',
        'bài giảng powerpoint',
        'chuyên đề',
        'đề thi',
        'trắc nghiệm đúng sai',
        'đăng ký mua',
        'hỗ trợ zalo',
        'aihay-ask-container',
        'vj-note'
    ]
    
    first_marker = None
    for tag in soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'ul', 'ol']):
        # If it's a div, check its ID or class
        tag_id = tag.get('id', '')
        tag_classes = tag.get('class', [])
        if tag_id == 'aihay-ask-container' or any(c in tag_classes for c in ['vj-note', 'box-new', 'box-slide', 'bottomgooglead']):
            first_marker = tag
            break
            
        text = tag.get_text(strip=True).lower()
        if not text:
            continue
            
        # Match only if the text starts with one of the end markers
        matched = False
        for marker in end_markers:
            if text.startswith(marker):
                matched = True
                break
                
        if matched:
            first_marker = tag
            break
            
    if first_marker:
        # Traverse up to the container level, but don't traverse beyond main container div
        root_marker = first_marker
        while root_marker.parent and root_marker.parent.name != '[document]':
            parent_classes = root_marker.parent.get('class', [])
            if any(c in parent_classes for c in ['col-md-7', 'middle-col', 'col-md-8', 'container']):
                break
            root_marker = root_marker.parent
            
        siblings = list(root_marker.next_siblings)
        root_marker.decompose()
        for sibling in siblings:
            if hasattr(sibling, 'decompose'):
                sibling.decompose()
                
    # 3. Clean up any remaining non-functional relative/JSP links and promo links
    for a in soup.find_all('a'):
        href = a.get('href', '').strip()
        text = a.get_text(strip=True)
        
        # If it's a relative link or points to a .jsp or is a promotional link
        if (href.startswith('..') or 
            '.jsp' in href or 
            href.startswith('/') or 
            (not href.startswith('http') and not href.startswith('#')) or
            'tailieugiaovien.com.vn' in href.lower() or
            'ai-hay.vn' in href.lower() or
            'zalo.me' in href.lower() or
            'vietjack.com' in href.lower()):
            
            # If the link has text, unwrap it (keep the text, remove the <a> wrapper)
            if text:
                a.unwrap()
            else:
                a.decompose()
                
    # 4. Clean up trailing/leading empty tags
    for tag in soup.find_all(['p', 'div', 'li', 'ul', 'ol', 'font', 'span']):
        if tag.get_text(strip=True) == '' and not tag.find_all('img'):
            tag.decompose()
            
    return str(soup)

# Process all topics in the database
cursor.execute("SELECT id, title, theory FROM topics")
rows = cursor.fetchall()

print(f"Starting database theory cleanup for {len(rows)} topics...")
modified_count = 0

for row in rows:
    topic_id, title, theory_html = row
    if not theory_html:
        continue
        
    cleaned_html = clean_theory_html(theory_html)
    
    # Update only if modified
    if cleaned_html != theory_html:
        cursor.execute(
            "UPDATE topics SET theory = %s WHERE id = %s",
            (cleaned_html, topic_id)
        )
        modified_count += 1
        print(f"  [+] Cleaned Topic {topic_id}: \"{title}\"")

db.commit()
print(f"\nCleanup complete! Total topics updated: {modified_count} / {len(rows)}")
cursor.close()
db.close()
