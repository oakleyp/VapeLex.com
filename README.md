# VapeLex.com

VapeLex.com was an online e-cigarette supply shop with PHP & MySQL backend and Authorize.net API payment gateway, built from the ground up with a custom ajax shopping cart and order management system, among a number of other painstakingly crafted features. 

This project was written over the course of four months in the summer of 2014 as an attempt to cash in on the new and rapidly growing e-cigarette market at the time. Due to inexperience in marketing and lack of any additional support as the sole proprietor, developer and designer, this shop only ever saw a total of three orders before the start of my freshman year at the University of Kentucky.

The goal of this site was to provide a far better user experience in terms of performance, aesthetics, and functionality than the relatively basic e-cigarrette sites that were popular at the time. Not including development of the product itself, which had custom labels, e-liquid recipes and packaging, more than 800 hours were devoted to this project to implement a huge number of scratch-built features:

Backend:
- User account system including automatic email verification, notifications, receipts, and USPS order tracking, maintained by a series of cron jobs (naively, authentication was entirely hand-built - with both client-side and server-side password hashing and salting functions as appropriate) 
- Integrated Authorize.net API checkout and payment gateway
- Custom persistent shopping cart system, with cookie and DB stores' synchronicity managed seamlessly between registered users and guests by a hand-built AJAX library 
- USPS shipping calculator with consideration for calculated weight by product quantity and type
- Prerequisite one-time age verification for entry, as was required for approval by Authorize.net
- Custom coupon system
- A one-time free sample request system
- Recaptcha integration
- Filterable full-text product search system
- User product rating and product stock availability system 

Frontend:
- Social media integration (Twitter, Facebook)
- Responsive jQuery, AJAX, and CSS3 frontend built on Bootstrap with mobile support

