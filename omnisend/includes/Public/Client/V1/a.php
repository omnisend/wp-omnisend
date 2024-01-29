<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */




$contact = new OmnisendContact();
$contact->setEmail('john.doe@example.com');
$contact->setFirstName('John');
$contact->setLastName('Doe');

// Send the contact to Omnisend
$apiClient = new Omnisend\ApiClient('YOUR_API_KEY');
$response = $apiClient->createContact($contact);

// Check the response
if ($response->isSuccess()) {
    echo 'Contact created and sent to Omnisend successfully.';
} else {
    echo 'Failed to create and send contact to Omnisend. Error: ' . $response->getErrorMessage();
}
