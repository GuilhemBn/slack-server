import sqlite3
conn = sqlite3.connect("/var/www/slack/resources.db")
c = conn.cursor()
c.execute('''DROP TABLE IF EXISTS kaamelott_quotes;''')
c.execute('''CREATE TABLE kaamelott_quotes(id INTEGER PRIMARY KEY, quote TEXT,author TEXT);''')
conn.commit()
conn.close()
