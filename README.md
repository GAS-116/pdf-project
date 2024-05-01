##Requirement
- PHP >= 7.2.5
- BCMath PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- MYSQL DB
- Redis

#Development
##without Virtualization(ie Docker)
(WIP:skipped for now. go to with Docker-Compose.)
TODO
- MYSQL DB
- Redis

###Preparation
copy .env.example to .env

##First Run App
- composer install
- php artisan migrate --seed
- php artisan passport:client --personal
- copy client ID to variable PERSONAL_ACCESS_CLIENT_ID in env file

##Run App
- composer install
- php artisan migrate

##How it works
TODO WIP
- signup for create a new account
- login for receive a token
- store config for setup sftp, ssh key should be sent in body of request
- create pdf schema for logic of generation pdf
- add necessary fonts for using
- send data for generating pdf


##with Docker-Compose

- PHP >= 7.2.5
- BCMath PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- MYSQL DB
- Redis

##Supervisor
- change {path_to_project} in worker.conf
- change {user} in worker.conf
- information for installing https://blog.whabash.com/posts/installing-supervisor-manage-laravel-queue-processes-ubuntu

##Horizon
- HORIZON_BASIC_AUTH_USERNAME, HORIZON_BASIC_AUTH_PASSWORD - variables for auth horizon
