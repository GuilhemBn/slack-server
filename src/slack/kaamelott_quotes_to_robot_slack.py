import wikiquote
import random
import requests
import urllib
import json
import sys
import argparse
import unidecode
from bs4 import BeautifulSoup

incoming_hook_url = "https://hooks.slack.com/services/CHANGEME" 


def get_image_url(character):
  character = character.replace(" ", "+")
  url = 'https://www.google.com/search?q='+urllib.parse.quote(character)+'+kaamelott&source=lnms&tbm=isch'
  res = BeautifulSoup(urllib.request.urlopen(urllib.request.Request(url, headers={'User-Agent':"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36"
})), "lxml")
  first_img = res.find_all("div", {"class":"rg_meta"})[0]
  link = json.loads(first_img.text)["ou"]
  return link

parser = argparse.ArgumentParser()
parser.add_argument("-s", "--schannel", type=str, help="Specify the Slack channel on which to publish", default="#random")
parser.add_argument("-c", "--character", type=str, help="Choose the Kaamelott character")
parser.add_argument("-q", "--quote", type=str, help="Specify a string that must be in the quote")

args = parser.parse_args()
#print(args.character)

quotes = wikiquote.quotes_and_authors('Kaamelott', lang="fr")
quotes = dict((q, a) for q, a in quotes.items() if a is not None and q is not None)

#print(quotes)

if args.character is not None:
  quotes = dict((q, a) for q, a in quotes.items() if a is not None and unidecode.unidecode(args.character.lower().strip().replace("’", "'")) in unidecode.unidecode(a.lower().strip().replace("’", "'")))

if args.quote is not None:
  quotes = dict((q, a) for q, a in quotes.items() if q is not None and args.quote.lower().strip() in q.lower())

if len(quotes) == 0:
  print(-1)
  sys.exit(-1)
else:

  quote, author = random.choice(list(quotes.items()))


  quote = quote.replace(u'\xa0', u' ')
  author = author.replace(u'\xa0', u' ')

  #print("{} - {}".format(quote, author))
  img_url = get_image_url(author)




  r = requests.post(incoming_hook_url, json={"text": quote, "username": author, "icon_url":img_url, "channel":args.schannel})
  print(0)
  sys.exit(0)
