## About Plan-Parser

there are 2 approaches to this 
1) Use artisan to eo make the model ``` php artisan make:model ClassName``` and we are good to go.
2) We use given template (stub) and reinvent the wheel

## Setup
1) we need to run `sail build` to build the container (how to set up sail (https://laravel.com/docs/9.x/sail#configuring-a-bash-alias))
2) we need vendor dir to work with this project. to do so `sail composer install`
3) finally `sail up` 

## Usage
Please provide a valid json format to `data.json` in `parser/` and the process is command base that will push a distinct job for every array to keep track of every failure, `sail artisan parse:file`
Note: No exception handling was done during the dev,

