# Branding

## TODOs

Next tasks:
- [x] Convert denial protection input in similar to processing time in style.
- [x] User can start from apply page.
- [x] Why 3i visa section
- [x] Backend: Persist form data in a specific table.
- [] Always use title "Apply for [country name] Visa" in steps.
- [] Rename pricing. Country incompatibilities.
- [] Display a "Pending state" page after payment approval.
- [] When payment is complete, ask user to set a password to finish account creation.
- [] Implement authentication, account creation.
- [] Implement emails.
- [] Open modal to keep data safe
- [] Open modal to learn more
- [x] Privacy policy page.
- [x] Terms of service page.
- [x] Implement payment gateway dialog when clicking continue to payment.
- [x] English, spanish and portuguese translations.
- [x] More reliable currency conversion. (once a day refresh maybe)
- [x] I want to receive 3i Visa updates, product launches and personalized offers. I can opt out anytime. Terms and Privacy Policy apply. should be saved into session as well.


# To update currencies:

```
php artisan currency:update --force
```

Add this to your server's crontab:
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1


Information on International Immigrant Visa

UI Components:
https://fluxui.dev/components/input

# Competitors

- https://ivisa.com
- https://www.submit-travelpermit.com/
- https://travelforms.online/
- https://colombiacm.visasyst.com/application
- https://www.travelvisapro.com/
