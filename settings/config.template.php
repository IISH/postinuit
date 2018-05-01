; Doresy configuration file

[settings]
admin_email = ; comma separated
from_email =
contact_name =
contact_email=
bcc_email = ; only during testing

mount_check_file = ; this file must exist on the mount
attachment_directory =  ; end with forward slash
deleted_attachment_directory = ; end with forward slash
wiki_directory = ; end with forward slash

mail_server_port = 25
mail_server_host =
mail_server_smtp_username =
mail_server_smtp_password =

url = ; end with forward slash

[db_default]
database =
username = root
password =
host = localhost
port =
driver = mysql
prefix =

[db_sync_knaw_ad]
database =
username = root
password = 
host = localhost
port =
driver = mysql
prefix = 
