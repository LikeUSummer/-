#encoding=utf-8
import itchat
from itchat.content import *
import os
import sys
import datetime
import re
import pymssql
import codecs

'''
#2.x版本需要下面的操作以避免乱码
reload(sys)
sys.setdefaultencoding('utf8')
'''
sys.path.append('./')#为了引入同目录的模块

TYPE_FRIEND=1
TYPE_GROUP=2
TYPE_FILEHELPER=3
TYPE_CONTENT={TEXT:1,SHARING:2,PICTURE:3,RECORDING:4,ATTACHMENT:5,VIDEO:6,VOICE:7}

save_url='C:/Test/WeChat/' #所有资源的存储目录，包括记录消息的文本文件、头像、聊天图片和音频等
db_addr='127.0.0.1'
db_user='sa' #肯定会有人吐槽为什么用SQL Server而不用MySQL，唉，开发的时候刚好装了SQL Server，懒得配置MySQL，你们稍作修改就OK啦
db_pwd='你的数据库密码'
conn=pymssql.connect(db_addr,db_user,db_pwd,database='WeChat',charset='utf8',autocommit=True)

#初始化数据库
cur=conn.cursor()
#建立朋友信息表
sql="""
	IF OBJECT_ID('Friends', 'U') IS NULL
		CREATE TABLE Friends(
			ID INT identity(1,1) primary key,
		    Name VARCHAR(64)
		)
"""
cur.execute(sql)
#建立web用户表
sql="""
	IF OBJECT_ID('Login', 'U') IS NULL
		CREATE TABLE Friends(
		    Name VARCHAR(64),
		    Password VARCHAR(64)
		)
"""
cur.execute(sql)
#创建一个用户
web_user_name='Summer'
web_user_pwd_md5='1a29c5b59644eb902a76833ded5cf184' #填入你初始密码的md5值
sql="SELECT * FROM Login WHERE Name='%s'" % (web_user_name);
cur.execute(sql)
row=cur.fetchone()
if not row:
	sql="INSERT INTO Login VALUES('%s','%s')" % (web_user_name,web_user_pwd_md5)
	cur.execute(sql)#创建自己的账号
cur.close()

def make_dir(dir):
	if not os.path.exists(dir):
		os.makedirs(dir)

def save_to_txt(friend_name,sender_name,content,date):
	with codecs.open(friend_name+'.txt', 'a', encoding='utf-8') as fp:
		fp.write(u'%s %s:\n%s\n\n' % (date,sender_name,content))

def save_to_one_txt(friend_name,sender_name,content,date):
	with codecs.open('history.txt', 'a', encoding='utf-8') as fp:
		if sender_name==u'我':
			fp.write(u'[%s]我=>%s:\n%s\n\n' % (date,friend_name,content))
		elif sender_name==u'你':
			fp.write(u'[%s]%s=>我:\n%s\n\n' % (date,friend_name,content))
		else:
			fp.write(u'[%s]%s,%s=>我:\n%s\n\n' % (date,friend_name,sender_name,content))

def save_chat_content(friend_name,sender_name,content,type,date):
	cursor=conn.cursor()
	sql="SELECT ID FROM Friends WHERE Name='%s'" % (friend_name)
	cursor.execute(sql)
	tab_name='F'+str(cursor.fetchone()[0])

	sql="""
	IF OBJECT_ID('%s', 'U') IS NULL
		CREATE TABLE %s(
			Sender VARCHAR(64),
		    Content VARCHAR(4096),
		    Type INT,
		    Time DATETIME
		)
	""" % (tab_name,tab_name)
	cursor.execute(sql)
	sql=u"INSERT INTO %s VALUES('%s','%s',%d,'%s');" % (tab_name,sender_name,content,type,date)
	cursor.execute(sql)
	#conn.commit()	

def save_friend_info(friend_name,user_name,type):
	cursor=conn.cursor()
	sql1="SELECT ID FROM Friends WHERE Name='%s'" % (friend_name)
	cursor.execute(sql1)
	row=cursor.fetchone()
	if not row:
		sql2="INSERT INTO Friends(Name) VALUES('%s')" % (friend_name)
		cursor.execute(sql2)
		cursor.execute(sql1)
		id=str(cursor.fetchone()[0])
		url=save_url + id + '/'
		make_dir(url)#为好友创建存储目录

		if type==TYPE_FRIEND:#好友头像
			img=itchat.get_head_img(userName=user_name)
		elif type==TYPE_GROUP:#群头像
			img=itchat.get_head_img(chatroomUserName=user_name)
		else:#暂时得不到文件助手的头像，直接返回
			return id
		file = open(url + id + ".jpg", 'wb')
		file.write(img)
		file.close()
		return id
	return str(row[0])

@itchat.msg_register([TEXT,SHARING,PICTURE,RECORDING,ATTACHMENT,VIDEO,VOICE], isFriendChat=True, isGroupChat=True)
def text_reply(msg):
	date=datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
	#获取发送者与接收者信息
	if msg['FromUserName']==itchat.search_friends()['UserName']:#如果自己是发送方
		user_name=msg['ToUserName']
		sender_name=u'我'
	else:
		user_name=msg['FromUserName']
		sender_name=u'你'

	id=-1
	#群聊
	if user_name[0:2]=='@@':
		friend_name=itchat.search_chatrooms(userName=user_name)['NickName']
		id=save_friend_info(friend_name,user_name,TYPE_GROUP)
		sender_name=msg['ActualNickName']
		if len(sender_name)==0:
			sender_name=u'我'
	elif user_name=='filehelper':#好友
		friend_name=u'文件助手'
		id=save_friend_info(friend_name,user_name,TYPE_FILEHELPER)
	else:
		friend=itchat.search_friends(userName=user_name)
		if not friend:
			return;#避开腾讯的新闻号等
		friend_name=friend['RemarkName']
		if len(friend_name)==0:#此好友没有备注，使用昵称代替
			friend_name=friend['NickName']
		id=save_friend_info(friend_name,user_name,TYPE_FRIEND)

	#获取消息内容
	if msg['Type']==TEXT:
		content=msg['Text']
	elif msg['Type']==SHARING:
		content=msg['Url']
	else:
		if msg['Type']==VOICE:
			content=datetime.datetime.now().strftime('%Y%m%d%H%M%S.mp3')
		else:
			content=msg['FileName']
		msg['Text'](save_url+id+'/'+content)#保存文件
	'''
	if sender_name==u'我':
		print(u'我=>%s:\n%s\n' % (friend_name,content))
	elif sender_name==u'你':
		print(u'%s=>我:\n%s\n' % (friend_name,content))
	else:
		print(u'[%s]%s=>我:\n%s\n' % (friend_name,sender_name,content))
	'''
	save_chat_content(friend_name,sender_name,content,TYPE_CONTENT[msg['Type']],date)#保存到数据库
	save_to_one_txt(friend_name,sender_name,content,date)#保存到文本文件

def exit():
	conn.close()

itchat.auto_login(enableCmdQR=True,hotReload=True,exitCallback=exit)
#import wx_robot #如果需要机器人定时给某些朋友发送消息，可以解开此注释，使用前请阅读wx_robot.py的代码并适当修改
itchat.run()#开启消息拦截
