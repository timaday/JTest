# JTest
Behat Test Automation Example - PHP


#### Setup
Install composer dependances
```
composer install
```

To get selenium running simply bring up the docker container
```
docker-compose up --build -d --remove-orphans 
```

To bring down the selenium container
```
docker-compose down -v
```
### Run suite
With selenium running in docker
```
bin/behat --suite jupiter
```
