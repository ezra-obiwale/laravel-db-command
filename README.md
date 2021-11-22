# laravel-db-command
A laravel command for database operations

## Introduction

This package creates a simple way to access and manipulate the application's database from the terminal.

## Usage

Run `php artisan db:table -h` to view list of functions.

## Examples

- Create: `php artisan db:table users -c --data=first_name:Ezra,last_name:Obiwale,status:3`
- Read: `php artisan db:table users --where=first_name:=:Ezra,status:=:3,deleted_at:null`
- Update: `php artisan db:table users -u --where=id:=:3 --data=status:4`
- Delete: `php artisan db:table users -d --where=id:=:3`
