# SNH-SecureBooksellingWebsite

## TODO
- [x] login
- [x] register
  - [X] email request and confirmation after registration
  - [X] link somewhere to the register form
- [x] logout
- [X] session expiration
- [x] listing purchasable books
- [x] add book to cart
  - [X] session upgrading
- [X] checkout with multistep
  - [X] the steps
      - [X] shipping address
      - [X] payment
      - [X] confirmation
      - [X] result
  - [X] add orders table with quantity
  - [X] manage quantity consistency with transactions
  - [X] handle authentication to download electronic books
- [X] password change
- [X] password reset
- [X] download purchased books
- [ ] SSL certificates on server
  - [ ] use a custom webserver (because https://stackoverflow.com/questions/27597670/how-to-enable-https-connections-in-phpstorms-built-in-web-server)
- [X] make it simpler to change the number of books in cart
- [X] show availability in book store
- [X] remove a single book from cart
- [X] add user check on books page 
- [X] impose strong password (zxcvbn php)
  - [X] also on password reset/change
- [X] add security lock in login 
- [ ] add remember me in login
- [X] add event logging 

## Maybe
- [ ] add a search bar
- [ ] add an admin panel to manage books