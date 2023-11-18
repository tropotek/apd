# APD




### TODO Tasks 17/11/23:

- 










### TODO System Tasks:

- [ ] Github now supports private repositories for free, move to private repository and use issues instead of this
todo file.
- [ ] Send only one email on user reg request email, log time email sent and do not send again for 1-2 days
- [ ] Fix session DB errors on reload after session timeout.

### DONE
- [x] Use SMTP to send emails so that the emails are signed correctly with DKIM. 
    - I have implemented DKIM on a per institution level, see settings page.
- [x] Update the APD contact form to email owner only, disable contact form on all subdomain accounts.
  all subdomain accounts should redirect to login/home page, no access to public pages for security.
    - Implemented a redirect to the main domain contact page for users clicking on the contact page 



