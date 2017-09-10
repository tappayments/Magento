# Magento v2.x

Supported Versions
Magento supported version 2.0.X onward

Installation and Configuration
upload app/code/Gateway (all files and folder) at you server end

# Run below command:
	1. php bin/magento module:enable Gateway_Tap
	2. php bin/magento setup:upgrade
	3. php bin/magento setup:static-content:deploy


goto Admin->Store->Configuration->Sales->Payment Method->Tap, and fill the details here and save them
	* Title - Tap
	* Merchant ID - 1014
	* Username - test
	* API Key - 1tap7
	* Test Mode - Yes

goto Admin->System->Cache Management and Clear all Cache

Now you can collect payment via Tap