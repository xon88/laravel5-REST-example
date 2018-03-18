
# laravel5-REST-example

This is a simple REST API created with [Laravel 5.1](https://laravel.com/) to show the basics of how to go about setting up migrations, seeds, routes, controllers, middleware and models. The project does not include any authentication as it was developed as a basic in-house example. It was initially started with the use of Eloquent ORM and Query Builder, which functionality is in-built to the fresh laravel installation, however it was then completely re-factored to avoid the use of ORMs and Query Builders, and all SQL queries were re-written as raw queries.


## Getting Started

### Prerequisites

The API was tested on a CentOS 7 VM as a PHP webserver. Laravel 5.1 requires php 5.6. Below are all instructions of how to setup this VM from scratch. If you already have an environment setup, the requirements are:
* Apache webserver
* MySQL (MariaDB on CentOS)
* Git
* Composer
* Laravel dependencies
  * PHP 5.6
  * php-mbstring & php-mcrypt

<details><summary> **CLICK HERE for Instructions**</summary>
<p>

* Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
* [Filezilla](https://filezilla-project.org/download.php) and [Notepad++](https://notepad-plus-plus.org/) might also prove useful.
* Download [CentOS7 64-bit ISO](https://www.centos.org/download/)


[Youtube Instructions:](https://www.youtube.com/watch?v=O1nF7HQAJGw)
* Create a new Linux RedHat 64-bit VM (default 1GB RAM and 8GB HDD are enough).
* Start VM and Install CentOS7 simple PHP webserver installation
* If you do not get an option for 64-bit, you need to enable Virtualization settings from your BIOS.
* Start VM and Install CentOS7 simple PHP webserver installation
  * Basic Web Server -> Tick PHP Support
  * Choose correct time
  * Setup root password and users
  * Change VM's Network Settings to Bridged Network Connection


Login as root and run:
```
yum install php-common
```

Open Firewall for Apache and register as service:
```
firewall-cmd --permanent --add-service=http
firewall-cmd --reload
systemctl enable httpd
```

Install MariaDB and register as service

>Check if installed:
```
rpm -qa | grep mariadb
```
>Install:
```
yum install mariadb-server
systemctl enable mariadb.service
```
Restart VM and login as root again
```
reboot
```

Open my.cnf file in any text editor. Add "skip-grant-tables" (without quotes) at the end of [mysqld] section and save the file
```
vi /etc/my.cnf
    
[mysqld]
skip-grant-tables
```
Enable External connections to the database:
```
firewall-cmd --permanent --add-service=mysql
firewall-cmd --reload
```

Run:
```
yum -y install php-mysql
yum -y update
```
Install SFTP and enable as service:
```
yum -y install vsftpd

systemctl enable vsftpd

firewall-cmd --permanent --add-port=21/tcp
firewall-cmd --reload
```
Creating Virtual Hosts Settings:
```
mkdir /etc/httpd/sites-available
mkdir /etc/httpd/sites-enabled
```
Open conf file:
```
vi /etc/httpd/conf/httpd.conf
```
>Put the following at the end of file:
```
IncludeOptional sites-enabled/*.conf
```
>Change
```
Directory /var/www/
...
AllowOverride None
...
/Directory
```
>to
```
Directory /var/www/
...
AllowOverride All
...
/Directory
```
>Save file and exit.

Install git:
```
yum -y install git-all
```

Install Composer Globally:
```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Install Laravel Dependencies:

>ext-mbstring:
```
yum -y install php-mbstring
```
>ext-mcrypt
```
yum -y install epel-release
yum -y install php-mcrypt*
```

Update from php 5.4 to 5.6:
https://withdave.com/2017/06/upgrading-php-5-6-x-later-centos7-via-yum-ius-repo/
```
yum install https://centos7.iuscommunity.org/ius-release.rpm
yum install yum-plugin-replace
yum replace --replace-with php56u php
```

Set Servername:
```
vi /etc/httpd/conf/httpd.conf
```
>Add the following line:
```
ServerName localhost
```
>Save and exit

</p>
</details>



### Installation

**Assumptions:**
* Pre-requisites above are met
* You have a CentOS machine (some of the following commands are CentOS-specific)
* You have a sites-available and sites-enabled Apache setup
* The following setup will be used (these can be changed according to your needs)
  * code will reside under /var/www/dev.local
  * hostname = dev.local
  * new database schema name = mydatabase
  * new database user & password = dbuser / mypassword
  * new linux user = projuser

Login as root.

Create Database and DB User:
```
mysql

grant all on *.* to ‘dbuser’@’%’ identified by ‘mypassword’ with grant option;

create database mydatabase;
	
quit
```

Create a file for your virtual host in /etc/httpd/sites-available:
```
vi /etc/httpd/sites-available/dev.local.conf

<VirtualHost *:80>
  ServerName  dev.local
  ServerAlias dev.local
  DocumentRoot /var/www/dev.local/public
  ErrorLog     /var/www/dev.local/logs/error.log
  CustomLog    /var/www/dev.local/logs/requests.log combined
</VirtualHost>
```
>[More Info](https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-centos-7)

Enable the hosts (symlink):
```
ln -s /etc/httpd/sites-available/dev.local.conf /etc/httpd/sites-enabled/dev.local.conf
```

Create project user:
```
adduser projuser
passwd projuser
```

Make sudoer and add to apache group:
```
usermod -aG wheel projuser
usermod -aG apache projuser
```
>By default, on CentOS, members of the wheel group have sudo privileges.

Create project folder:
```
mkdir /var/www/dev.local
chmod -R 775 /var/www
chown -R projuser:apache /var/www
```

Login as the project user:
```
su projuser
cd /var/www/dev.local
```

Make your user the owner
```
sudo chown -R projuser:apache /var/www/dev.local
```

Make sure directory is empty - if not empty it and also remove hidden files
```
rm -R /var/www/dev.local/*
rm -R /var/www/dev.local/.*
```

Clone repo - get clone link from github
(don't forget the . at the end to clone in current directory without creating a new folder)
```
git clone https://github.com/xon88/laravel5-REST-example.git .
```

Turn off git marking files as changed if permissions have changed
```
git config core.filemode false
git config --global core.filemode false
```

Create required directories:
```
mkdir vendor
mkdir logs
mkdir bootstrap/cache
sudo chmod -R 777 storage/
```
>777 is not a good idea here but for dev purposes should be fine


Only if using CentOS, you need to set write permissions in SELinux
```
su -c "chcon -R -h -t httpd_sys_script_rw_t /var/www/dev.local/storage/"
su -c "chcon -R -h -t httpd_sys_script_rw_t /var/www/dev.local/logs/"
```

Check that all is fine with apache by restarting it:
```
sudo systemctl restart httpd.service
```

Install dependencies:
```
composer install --no-dev
```

Create .env file
```
sudo cp .env.example .env
```

Clear cache:
```
php artisan cache:clear
composer dump-autoload
```

Make your user the owner and apache as group:
```
sudo chown -R projuser:apache /var/www/dev.local
```

Generate application key - should be written automatically to .env file
```
sudo php artisan key:generate
```

Edit .env file:
```
vi .env

...
APP_ENV=production
APP_DEBUG=false //set to true if you need to debug any problems
APP_KEY=[already set by previous command]

DB_HOST=localhost
DB_DATABASE=mydatabase
DB_USERNAME=dbuser
DB_PASSWORD=mypassword
...
```

Migrate and seed db:
```
php artisan migrate
php artisan db:seed
```

Restart apache:
```
sudo systemctl restart httpd.service
```

Check virtualhosts:
```
httpd -S
```

Note down the machine's IP address:
```
ip addr
```

Add virtual hosts in host file on your host OS
On windows: C:/Windows/System32/drivers/etc/hosts
Edit host file as Administrator and add line:
```
[yourVMsIPaddress] dev.local
```

## Usage (API Consumption)

[Postman](https://www.getpostman.com/) proved to be a good choice to test the consumption of an API.

Open Postman and import this collection (paste raw text):
<details><summary>**CLICK HERE for collection**</summary>
<p>

```
{
	"variables": [],
	"info": {
		"name": "laravel5-REST-example",
		"_postman_id": "51cf3fc8-1d44-c14f-ab5b-9557f6c9b9e8",
		"description": "Test for laravel5-REST-example",
		"schema": "https://schema.getpostman.com/json/collection/v2.0.0/collection.json"
	},
	"item": [
		{
			"name": "Customer",
			"description": "",
			"item": [
				{
					"name": "Get",
					"request": {
						"url": "dev.local/api/v1/customer",
						"method": "GET",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "menu_order",
									"value": "0",
									"type": "text"
								},
								{
									"key": "template_id",
									"value": "1",
									"type": "text"
								},
								{
									"key": "url",
									"value": "abc",
									"type": "text"
								},
								{
									"key": "target",
									"value": "def",
									"type": "text"
								},
								{
									"key": "is_link",
									"value": "1",
									"type": "text"
								},
								{
									"key": "menu_parent_id",
									"value": "0",
									"type": "text",
									"disabled": true
								},
								{
									"key": "menu_id",
									"value": "1",
									"type": "text",
									"disabled": true
								}
							]
						},
						"description": ""
					},
					"response": []
				},
				{
					"name": "Get one",
					"request": {
						"url": {
							"raw": "dev.local/api/v1/customer/:customer_id",
							"host": [
								"dev",
								"local"
							],
							"path": [
								"api",
								"v1",
								"customer",
								":customer_id"
							],
							"query": [],
							"variable": [
								{
									"key": "customer_id",
									"value": "1"
								}
							]
						},
						"method": "GET",
						"header": [],
						"body": {},
						"description": ""
					},
					"response": []
				},
				{
					"name": "Create",
					"request": {
						"url": "dev.local/api/v1/customer",
						"method": "POST",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "first_name",
									"value": "Joe",
									"type": "text"
								},
								{
									"key": "last_name",
									"value": "Borg",
									"type": "text"
								},
								{
									"key": "gender",
									"value": "M",
									"type": "text"
								},
								{
									"key": "country_code",
									"value": "MT",
									"type": "text"
								},
								{
									"key": "bonus_parameter",
									"value": "10",
									"type": "text",
									"disabled": true
								},
								{
									"key": "email",
									"value": "joe@joe.com",
									"description": "",
									"type": "text"
								},
								{
									"key": "real_money_balance",
									"value": "0",
									"type": "text",
									"disabled": true
								},
								{
									"key": "bonus_balance",
									"value": "0",
									"description": "",
									"type": "text",
									"disabled": true
								}
							]
						},
						"description": ""
					},
					"response": []
				},
				{
					"name": "Update",
					"request": {
						"url": {
							"raw": "dev.local/api/v1/customer/:customer_id",
							"host": [
								"dev",
								"local"
							],
							"path": [
								"api",
								"v1",
								"customer",
								":customer_id"
							],
							"query": [],
							"variable": [
								{
									"key": "customer_id",
									"value": "1"
								}
							]
						},
						"method": "PUT",
						"header": [
							{
								"key": "Content-Type",
								"value": "application/x-www-form-urlencoded",
								"description": ""
							}
						],
						"body": {
							"mode": "urlencoded",
							"urlencoded": [
								{
									"key": "first_name",
									"value": "Josephine",
									"type": "text"
								},
								{
									"key": "last_name",
									"value": "Borg",
									"type": "text"
								},
								{
									"key": "gender",
									"value": "F",
									"type": "text"
								},
								{
									"key": "country_code",
									"value": "GB",
									"type": "text"
								},
								{
									"key": "bonus_parameter",
									"value": "20",
									"type": "text"
								},
								{
									"key": "email",
									"value": "joe@joe.com",
									"description": "",
									"type": "text"
								},
								{
									"key": "real_money_balance",
									"value": "0",
									"type": "text",
									"disabled": true
								},
								{
									"key": "bonus_balance",
									"value": "0",
									"description": "",
									"type": "text",
									"disabled": true
								}
							]
						},
						"description": ""
					},
					"response": []
				}
			]
		},
		{
			"name": "Customer Deposit",
			"description": "",
			"item": [
				{
					"name": "Get",
					"request": {
						"url": {
							"raw": "dev.local/api/v1/customer/:customer_id/deposit",
							"host": [
								"dev",
								"local"
							],
							"path": [
								"api",
								"v1",
								"customer",
								":customer_id",
								"deposit"
							],
							"query": [],
							"variable": [
								{
									"key": "customer_id",
									"value": "1"
								}
							]
						},
						"method": "GET",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "menu_order",
									"value": "0",
									"type": "text"
								},
								{
									"key": "template_id",
									"value": "1",
									"type": "text"
								},
								{
									"key": "url",
									"value": "abc",
									"type": "text"
								},
								{
									"key": "target",
									"value": "def",
									"type": "text"
								},
								{
									"key": "is_link",
									"value": "1",
									"type": "text"
								},
								{
									"key": "menu_parent_id",
									"value": "0",
									"type": "text",
									"disabled": true
								},
								{
									"key": "menu_id",
									"value": "1",
									"type": "text",
									"disabled": true
								}
							]
						},
						"description": ""
					},
					"response": []
				},
				{
					"name": "Create",
					"request": {
						"url": {
							"raw": "dev.local/api/v1/customer/:customer_id/deposit",
							"host": [
								"dev",
								"local"
							],
							"path": [
								"api",
								"v1",
								"customer",
								":customer_id",
								"deposit"
							],
							"query": [],
							"variable": [
								{
									"key": "customer_id",
									"value": "1"
								}
							]
						},
						"method": "POST",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "amount",
									"value": "10",
									"type": "text"
								}
							]
						},
						"description": ""
					},
					"response": []
				}
			]
		},
		{
			"name": "Customer Withdrawal",
			"description": "",
			"item": [
				{
					"name": "Get",
					"request": {
						"url": {
							"raw": "dev.local/api/v1/customer/:customer_id/withdrawal",
							"host": [
								"dev",
								"local"
							],
							"path": [
								"api",
								"v1",
								"customer",
								":customer_id",
								"withdrawal"
							],
							"query": [],
							"variable": [
								{
									"key": "customer_id",
									"value": "1"
								}
							]
						},
						"method": "GET",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "menu_order",
									"value": "0",
									"type": "text"
								},
								{
									"key": "template_id",
									"value": "1",
									"type": "text"
								},
								{
									"key": "url",
									"value": "abc",
									"type": "text"
								},
								{
									"key": "target",
									"value": "def",
									"type": "text"
								},
								{
									"key": "is_link",
									"value": "1",
									"type": "text"
								},
								{
									"key": "menu_parent_id",
									"value": "0",
									"type": "text",
									"disabled": true
								},
								{
									"key": "menu_id",
									"value": "1",
									"type": "text",
									"disabled": true
								}
							]
						},
						"description": ""
					},
					"response": []
				},
				{
					"name": "Create",
					"request": {
						"url": {
							"raw": "dev.local/api/v1/customer/:customer_id/withdrawal",
							"host": [
								"dev",
								"local"
							],
							"path": [
								"api",
								"v1",
								"customer",
								":customer_id",
								"withdrawal"
							],
							"query": [],
							"variable": [
								{
									"key": "customer_id",
									"value": "1"
								}
							]
						},
						"method": "POST",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "amount",
									"value": "10",
									"type": "text"
								}
							]
						},
						"description": ""
					},
					"response": []
				}
			]
		},
		{
			"name": "Report",
			"description": "",
			"item": [
				{
					"name": "Transactions by Date and Country",
					"request": {
						"url": "dev.local/api/v1/report/transactions",
						"method": "POST",
						"header": [],
						"body": {
							"mode": "formdata",
							"formdata": [
								{
									"key": "from",
									"value": "10/03/2018",
									"type": "text",
									"disabled": true
								},
								{
									"key": "to",
									"value": "17/03/2018",
									"description": "",
									"type": "text",
									"disabled": true
								}
							]
						},
						"description": ""
					},
					"response": []
				}
			]
		}
	]
}
```

</p>
</details>


Start sending requests and enjoy =)


## API Specifications

### Task
>Implement a REST API that shall be used internally, so no authentication is needed. Format shall be JSON. No front-end or graphical interface is
needed. Please use any DBAL such as PDO but do not use an ORM or query builder (such as Doctrine or Eloquent).
>
>We need the following functionality/endpoints:
>
>* Add new customer (gender, first name, last name, country, email). Each customer during creation should have assigned a random bonus parameter between 5% and 20%. Email shall be unique.
>* Edit customer details given on registration. For each customer the following operations shall be possible:
>>>* Deposit money. Every 3rd deposit of the customer should be awarded with bonus on the deposit amount according to his bonus parameter. For instance, if a customer with 10% bonus is making a deposit of 100 EUR, his balance shall increase by 110 EUR. Bonus balance needs to be kept separate from real money balance.
>>>* Withdraw money. Customer balance can never go below 0 and bonus money cannot be withdrawn. For instance, if customer balance is
110 EUR, but 10 EUR is bonus money, the maximum withdrawal amount is 100 EUR.
>* Reporting endpoint generating a list of the total deposits and withdrawals (unique customers doing at least one deposit or withdrawal,
number and total amount of both, deposits and withdrawals) per country and date for a given time frame (default: last 7 days).
>>>* Example:
>>>
>>>
>>>| Date       	| Country 	| Unique Customers 	| No of Deposits 	| Total Deposit Amount 	| No of Withdrawals 	| Total Withdrawal Amount 	|
>>>|------------	|---------	|------------------	|----------------	|----------------------	|-------------------	|-------------------------	|
>>>| 2015-05-06 	| MT      	| 32               	| 45             	| 456.34               	| 24                	| -200.45                 	|
>>>| 2015-05-06 	| DE      	| 16               	| 14             	| 65.32                	| 6                 	| -456.34                 	|
>
>* Financial operations (deposit/withdrawal) needs to be implemented in a way that ensures data integrity also for situations where different transaction requests are made at the same moment.


### Omissions, vulnerabilities, future enhancements
* No security and authentication is implemented
* Currency is not implemented
* Validation is basic and not too much attention was given to SQL sanitization.
* Only PUT full row updates are implemented (vs. PATCH = partial update)
* No DELETE endpoints are implemented


### Endpoints

#### Customers

- **<code>GET</code> [hostname]/api/v1/customer**

	Returns a list of all customers.

- **<code>GET</code> [hostname]/api/v1/customer/[customer_id]**

	Returns the record of customer with [customer_id]
    
- **<code>POST</code> [hostname]/api/v1/customer**

	Creates a new customer by passing the following attributes as **form-data** in the body. Returns the new customer record.
    * <code>first_name</code>
    * <code>last_name</code>
    * <code>gender</code> M=Male, F=Female, O=Other, U=Unknown
    * <code>country_code</code> eg. MT=Malta
    * <code>email</code> Must be unique
    * <code>bonus_parameter</code> Optional - The percentage deposit bonus to apply at every 3rd deposit (5-20, default random)
    * <code>real_money_balance</code> Optional (default 0)
    * <code>bonus_balance</code> Optional (default 0)

- **<code>PUT</code> [hostname]/api/v1/customer/[customer_id]**

	Updates the customer specified with [customer_id], by passing the following attributes as **x-www-form-urlencoded** in the body. Returns the updated customer record.
    Header **Content-Type** must be set as **application/x-www-form-urlencoded**.
    * <code>first_name</code>
    * <code>last_name</code>
    * <code>gender</code> M=Male, F=Female, O=Other, U=Unknown
    * <code>country_code</code> eg. MT=Malta
    * <code>email</code> Must be unique
    * <code>bonus_parameter</code> 5-20
    * <code>real_money_balance</code> Optional
    * <code>bonus_balance</code> Optional


#### Deposits

- **<code>GET</code> [hostname]/api/v1/customer[customer_id]/deposit**

	Returns a list of all the customer's deposits.

- **<code>POST</code> [hostname]/api/v1/customer/[customer_id]/deposit**

	Creates a new deposit for the customer with [customer_id] and updates his balance, by passing the following attributes as **form-data** in the body. Returns the updated customer record.
    * <code>amount</code> >0 Amount to be deposited.


#### Withdrawals

- **<code>GET</code> [hostname]/api/v1/customer[customer_id]/withdrawal**

	Returns a list of all the customer's withdrawals.

- **<code>POST</code> [hostname]/api/v1/customer/[customer_id]/withdrawal**

	Creates a new withdrawal for the customer with [customer_id] and updates his balance (if enough funds are available), by passing the following attributes as **form-data** in the body. Returns the updated customer record.
    * <code>amount</code> >0 Amount to be withdrawn.


#### Reporting

- **<code>POST</code> [hostname]/api/v1/report/transactions**

	Retrieves a new report counting and summing the deposits and withdrawals of unique customers who have made at least 1 transaction during the period of days specified (defaults to last 7 days if not specified). Groups by date and customer's country. Optionally dates can be specified by passing the following attributes as **form-data** in the body:
    * <code>from</code> Report Start Date (format dd/mm/yyyy - default 7 days ago)
    * <code>to</code> Report End Date (format dd/mm/yyyy - default today)


## Good to know:

If you encounter any problems try disabling SELinux (CentOS). This is not permanent, so they will be reset to default status after reboot, but it will help identify if the problems you are encountering are being caused by SELinux.

>Disable:
```
setenforce 0
```
>Enable:
```
setenforce 1
```


Changing IP of the VM (CentOS)
```
sudo dhclient -r
sudo dhclient
```


Dropping and migrating a fresh database:
```
su projuser
cd /var/www/dev.local

mysql
drop database mydatabase;
create database mydatabase;
quit

composer dump-autoload
php artisan cache:clear
php artisan migrate
php artisan db:seed
```

## License

[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://badges.mit-license.org)

- **[MIT license](http://opensource.org/licenses/mit-license.php)**
- Copyright 2018 © Shawn Xuereb


