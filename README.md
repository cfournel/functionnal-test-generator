Functional test Generator Bundle
================================

[![Build Status](https://secure.travis-ci.org/huitiemesens/functionnal-test-generator.svg)](https://secure.travis-ci.org/#!/huitiemesens/functionnal-test-generator.svg)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d32c8d7f-2366-436c-91fe-9f14031154a5/mini.png)](https://insight.sensiolabs.com/projects/d32c8d7f-2366-436c-91fe-9f14031154a5)

**Lead Developer** : [@huitiemesens](https://github.com/huitiemesens)

function-generate-bundle allows you to easily generate skeleton for your functional tests.

**Currently supported :**
* **Authentification** : 
  * You can add authentication to your tests to get access to secured routes. (Check step 6 below)
  * This bundle require ["liip/functional-test-bundle](https://packagist.org/packages/liip/functional-test-bundle)" to provide authenticated client
* **Fixtures** : This bundle require
  * You can load fixtures inside your tests (check step 5 below)
  * ["liip/functional-test-bundle](https://packagist.org/packages/liip/functional-test-bundle)" to provide authenticated client
* **Route** : Any routes that fits the Bundle:Controller:actionName format

**Future releases :** (feel free to PR if you want to help)
* **Route** : default variable for routes
* **POST Form** : Generate form based on annotations for given route
* **More Basic Tests** : Currently the skeleton generates tests with isSuccessful() asset only. I'd like to propose more options to get more basic tests.

Installation
============
---
1) Download via composer the bundle

       composer require huitiemesens/functionalTestGeneratorBundle
2) Add the bundle to your AppKernel.php under your dev/test environnement

       new huitiemesens\FunctionalTestGeneratorBundle\FunctionalTestGeneratorBundle(),

3) call the command from your console. Example to generate all tests for controller inside your blogBundle :
   
       php app/console tests:generate acme:BlogBundle
    
4) Confirm for each controller inside BlogBundle to generate tests.
5) You can add fixtures to your tests. Go to your freshly SetUpFunctionalTest.php ( inside newly created Tests yourBundle ) and go to executesFixtures() function. You can add you declared fixtures inside the getFixtures->() line 192. Array is expected.
6) An authentication credentials is expected: Put unit_test_password and unit_test_email values in your parameters.yml to allow authentication to your tests.

License
-------

This bundle is available under the [MIT license](LICENSE).
