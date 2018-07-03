# Catalog of goods
Catalog of goods with edit/delete existed goods and add new goods.

### Client application
Client allication was created via React.js.
Start development:
```
cd dev/client
npm install
cd src
cp config.js.template config.js
npm run start
```
Create production build:
```
cd dev/client
npm install
cd src
cp config.js.template config.js
npm run build
```
Copy files from `dev/client/build/` to root.
Copy file `.htaccess` from `dev/` to root.

### Server application
Server allication was created via PHP and Apache.
Start application:
```
cd dev/api/libs
cp bd.php.template bd.php
```
Copy folder `api` from `dev/` to root.

### Database
MySQL is used to manage data.
Create new table:
```
CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `price` float NOT NULL,
  `img` text NOT NULL
);
```

### API requests
GET `/api/goods?limit=100,offset=0&sorting=id&order=desc`
Sorting is 'id' or 'price'.
Order is 'asc' or 'desc'.
Response (json): 
```
{
    total: 1,
    goods: [
        {
            id: 1,
            title: 'title',
            description: 'description'
            price: 1234,
            img: 'url'
        }
    ],
    offset: 0,
    limit: 100,
    sorting: 'id',
    order: 'desc'
}
```

POST `/api/goods`
Body type is 'JSON'.
Body:
```
{
    title: 'title',
    description: 'description'
    price: 1234,
    img: 'url'
}
```
Response (json): 
```
{
    id: 1,
    title: 'title',
    description: 'description'
    price: 1234,
    img: 'url'
}
```

PUT `/api/goods/:id`
Body type is 'JSON'.
Body:
```
{
    id: 1,
    title: 'title',
    description: 'description'
    price: 1234,
    img: 'url'
}
```
Response (json): 
```
{
    id: 1,
    title: 'title',
    description: 'description'
    price: 1234,
    img: 'url'
}
```
DELETE `/api/goods/:id`

Response (json): 
```
{
    message: "Good $id was deleted"
}
```