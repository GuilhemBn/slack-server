import bs4
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("-f", "--filename", help="HTML file to append", type=str)
parser.add_argument("-t", "--text", help="text to append", type=str)
args = parser.parse_args()

with open(args.filename) as f:
    txt = f.read()
    soup = bs4.BeautifulSoup(txt, "lxml")

new_p_tag = soup.new_tag('p', style="padding:0;margin:0;color:#000000;font-size:11pt;font-family:&quot;Arial&quot;;line-height:1.15;orphans:2;widows:2;text-align:left")
new_span_tag = soup.new_tag('span', style="color:#000000;font-weight:400;text-decoration:none;vertical-align:baseline;font-size:11pt;font-family:&quot;Arial&quot;;font-style:normal")
new_span_tag.append("- " + args.text)
new_p_tag.append(new_span_tag)
soup.body.append(new_p_tag)
html =soup.contents
html = soup.prettify("utf-8")
with open(args.filename, "wb") as f:
   f.write(html)

