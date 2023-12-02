# APD

### Tasks Completed
- Added a new student contact System (Manager/Edit)
- Updated Case edit page to use new student field
- Update Client data fixing names and removing duplicates where possible
- Create new Client Contacts from the Client email CC field
- Added new Company and CompanyContact System
- Update Case edit page to use new Company (Client) table
- Added CompanyContacts filed to Case edit page with ability to create new contact for company 
- Added alert to dashboard for pathologists stating how many non-completed case they have
- Security and bugfix updates to base libraries
- Added autocomplete to Case fields 'species', 'Owner' and 'Colour'
- Removed old Contact system
- Remove client hover infobox, replace with a panel on the left that shows client and contact info that dynamically updates.
- Update staff manager/edit added disable field instead of delete.
- Fixed masquerade logout bug not returning to the correct URL
- Added new permission "Review Cases" These users will see a checkbox in a case so they can mark a case as reviewed
- Added reviewed by checkbox that when clicked adds the user as teh reviewer for a case
- Added reviewer credentials to the PDF report under the pathologist section
- Fixed Disposal reminder emails
- Updated email text content messages to be more readable
- Set all report_status to completed for completed cases



### TODO Tasks 17/11/23:

__Release Notes:__

- Backup the DB before releasing
- Release the updates with `./bin/cmd ug`
- (TODO: Confirm with Smitha if we should complete old cases) Run the command `./bin/cmd cc` if OK
- 

__Update Questions/Notes__
- Does not look like staff are completing cases, a lot seem to stop on `examined`, along with the reminder emails we could:
  - Added an alert to the dashboard showing how many cases the pathologist has that are not completed, is this OK?
  - We can mark case's as `completed` if the `account_status` is set to `invoiced` and all requests are complete.

- For the dashboard we have the cases table and the requests table, I assume the cases table is mostly used 
by the Pathologists and the requests table is used by the Technicians is this correct?
Who are users the general users that are not a Path or Tech, what permissions should they have?

- The reportStatus for a lof of cases is left at `interim` we could set the report status to completed when 
the case is set to completed? Or not allow the case to be set to completed until the report is set first? 
In this update I have auto set completed cases to report_status to completed so we do not have as 
many initial reminders being sent. If we also run the script to complete old cases this will also reduce the 
number of reminders sent initially.






__TESTING__

- Compare the live site and the dev site Clients and client contacts to see if they correctly match up.
- 


__Chargeable Updates__

- ~~Create a single Client object/table containing Client and additional contact details. (Manager, Edit, etc)~~ [8hrs] 
    - ~~Client record will have multiple contact's containing contact `name`, `email`, `phone`.~~
- ~~Remove adding new client through Case edit page, only technicians and pathologists can add clients from the new client manager.~~ [1hrs]
- ~~Add dialog to create new client contact in case edit page~~ [1hrs]
- ~~When a user selects the submitting client, add another select field for `clientContact` where the user can select/create a new contact.
Auto-populate contact select field when new client is selected.~~ [4hrs]
    - ~~Reconcile Client contacts removing duplicates (do before Creating new Client table)~~
    - ~~Create script to update existing cases to use new Client ID's~~ 
[14hrs total]

- ~~Add a Student table/object and copy the data from the Contacts table, Update code to use new Student object. (Manager, Edit, etc)~~ [4hrs]

- ~~Add a `reviewedById` field that links to a user that has a new permission `Can Review Case`,
  add field after `Addendum` textarea on the reporting tab.~~ [2hrs]
- ~~The reviewer credentials should then be added to the report PDF at the end of the document.~~ [2hrs]

- ~~For necropsy cases add a `necropsyPerformedOn` date field (consider adding a `Necropsy Complete` button to add today's date),~~
  - Send a reminder to the pathologist (CC site admin) after 15 working days if case not completed. [4hrs]

- For biopsy cases when all Histology requests are completed, send a reminder to pathologist (cc site admin) after 24 hours  
to `complete` the __`report`__ if not completed already. [4hrs] 


----
Total of 26 hours @ $100ph ($2,600)
----

__No Charge - System Updates__

- Look into creating a merge case function in the company manager, would be good to merge all cases from one company to another
or optionally on delete have a company select that migrates all cases to the selected company on delete.

- ~~Fix the Client field hover getting in the way. Maybe a longer timeout or only on click, update layout of panel.~~ [1hrs]
   - ~~The situation has changed with company and contacts and we need a new way to display these details.~~ 
- Remove outdated Contact system once updates are completed. [4hrs]
  - Check client field in old cases is correct and entered, if not create script to populate case field on old cases.
  - ~~Remove the setting to select owner text field and other code using it.~~
- ~~Check the masquerade bug on logout not going back to correct page left from.~~ [1hrs]
- ~~Fix staff delete/disable, review delete option in place of deactivate Check that it is obvious to remove a staff member.
  Test what happens when we delete a staff.~~ [2hrs]







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



