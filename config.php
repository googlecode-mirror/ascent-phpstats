<?php
###################################################################################################
#
#                   N E W     P U B L I C    SVN 
#           http://ascent-phpstats.googlecode.com/svn/trunk/
#
###################################################################################################

###################################################################################################
#
#         S I T E     C O N F I G     P A R T
#
###################################################################################################

$_CONFIG['stats.xml']="./xml/stats.xml";         //path to stats.xml generated by server
                                                 //to use stats.xml via web you must put 
                                                 //anything like that: "http://localhost/stats.xml"
$_CONFIG['tpl']="./tpl/";                        //path to .tpl file
$_CONFIG['tpl_filename']="main.tpl";             //.tpl filename
$_CONFIG['force_gzip']=false;                    //Gzip-compression
$_CONFIG['logo']='icon/logo.jpg';                // false - disable
$_CONFIG['max_acc_per_ip']=1;                    //how many accouns user can create by one IP

###################################################################################################
#
#         D A T A B A S E     C O N F I G     P A R T
#
###################################################################################################

 #charDB
$_CONFIG['MySQL_char_host']="CHANGE THIS";       //MySQL database adress 
$_CONFIG['MySQL_char_user']="CHANGE THIS";       //MySQL username
$_CONFIG['MySQL_char_password']="CHANGE THIS";   //MySQL password
$_CONFIG['MySQL_char_db']="ascent";              //your ascent char database name
 #loginDB
$_CONFIG['MySQL_login_host']="CHANGE THIS";      //MySQL database adress 
$_CONFIG['MySQL_login_user']="CHANGE THIS";      //MySQL username
$_CONFIG['MySQL_login_password']="CHANGE THIS";  //MySQL password
$_CONFIG['MySQL_login_db']="ascent";             //your ascent login database name

###################################################################################################
#
#         S A T I S T I C S     C O N F I G     P A R T
#
###################################################################################################

$_CONFIG['lvlmin']=3;                            //min character level which will not be count in total stats
$_CONFIG['statistics_update_time']=18000;        //time to update stats from database
$_CONFIG['reg_e']=3;                             //it use to set how many diff symbols need to register account if password like login
                                                 //if my english so bad do not touch this ;P
?>