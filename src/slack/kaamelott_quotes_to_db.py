import wikiquote
import sqlite3

DB_FILE_NAME = "/var/www/slack/resources.db"
DB_KAAMELOTT_TABLE = "kaamelott_quotes"

quotes = wikiquote.quotes_and_authors('Kaamelott', lang="fr")
quotes = dict((q, a) for q, a in quotes.items() if a is not None and q is not None)

if len(quotes) == 0:
  sys.exit(-1)
else:
  conn = sqlite3.connect(DB_FILE_NAME)
  c = conn.cursor()
  c.execute("DELETE FROM "+DB_KAAMELOTT_TABLE+";")
  for q, a in quotes.items():
    quote = q.replace(u'\xa0', u' ').replace("'", "''")
    author = a.replace(u'\xa0', u' ').replace("'","''")
    c.execute("INSERT INTO {} (quote, author) VALUES ('{}', '{}');".format(DB_KAAMELOTT_TABLE, quote, author))
  conn.commit()
  conn.close()
