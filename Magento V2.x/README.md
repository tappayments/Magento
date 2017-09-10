# Tap_Magento_v2.x_Kit

Supported Versions
Magento supported version 2.0.X onward

Installation and Configuration
upload app/code/Gateway (all files and folder) at you server end

Run below command:
php bin/magento module:enable Gateway_Tap
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy


goto Admin->Store->Configuration->Sales->Payment Method->Tap
fill details here and save them
		* Title - Tap
		* Merchant ID - 1014
        * Username - test
        * API Key - 1tap7
        * Test Mode - Yes

goto Admin->System->Cache Management
Clear all Cache

Now you can collect payment via Tap