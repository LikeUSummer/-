#encoding=utf-8
import itchat
from itchat.content import *
import requests
from bs4 import BeautifulSoup
import random
import datetime
from apscheduler.schedulers.background import BackgroundScheduler

#这个脚本是老套路了，给喜欢的人每天定时发送天气信息+一段美文，我选的是中国天气网和美文阅读网来爬取信息
#你如果不想用于酸酸的爱情，可以改成友情和基情类的，随意搭配

http_header={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36'
    }

def get_html_text(html):
	encoding = 'utf-8'
	if html.encoding == 'ISO-8859-1':
		encodings = requests.utils.get_encodings_from_content(html.text)
		if encodings:
			encoding = encodings[0]
		else:
			encoding = html.apparent_encoding
	text = html.content.decode(encoding, 'replace').encode('utf-8', 'replace')
	return text

def get_motto():
	url="https://www.duanwenxue.com/yuju/weimei/list_"+str(random.randint(1,400))+".html" #美文阅读网
	#url='http://wufazhuce.com' #韩寒的one网站上的每日一句
	html=requests.get(url,http_header)
	soup=BeautifulSoup(html.text,'lxml')

	motto=soup.find('div',class_='list-short-article').find_all('p')[random.randint(0,20)].find('a',attrs={'target':'_blank'}).text
	#motto=soup.find_all('div',class_='fp-one-cita')[0].find('a').text
	return motto+'\n'

def get_weather():
	url='http://www.weather.com.cn/weather/101200101.shtml'
	html=requests.get(url,http_header)
	soup = BeautifulSoup(get_html_text(html),'lxml')
	today=soup.find('ul', attrs={'class': 't clearfix'}).find('li')
	weather = today.find('p',attrs={'class': 'wea'}).getText()
	temp2= today.find('p',attrs={'class': 'tem'}).find('span').getText()
	temp1 = today.find('p',attrs={'class': 'tem'}).find('i').getText()
	temp = temp2+"/"+temp1
	win = today.find('p',attrs={'class': 'win'}).find('i').getText()

	return u'天气:'+weather+u'\n温度:'+temp+u'\n风速:'+win+'\n'

def is_online():
	try:
		if itchat.search_friends():
			return True
	except:
		return False
	return True

def keep_online():
	if is_online():
		return	True
	for i in range(5):
		itchat.auto_login()
		if is_online():
			return True
	return False

def send_msg():
	if keep_online():
		#print(get_motto())
		friend = itchat.search_friends(name=u'sunrise')[0]
		date=datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S\n')
		friend.send(date+u"祝你风光，举世无双\n"+get_weather()+get_motto()+u'——来自爱你的robot')
	else:
		print(u'账号无法登录...')

scheduler = BackgroundScheduler()
scheduler.add_job(send_msg,'cron',hour='6,9,12,15,18,21',minute=0)
scheduler.start()
