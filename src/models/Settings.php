<?php

namespace pragmatic\cookies\models;

use craft\base\Model;

class Settings extends Model
{
    // General - Popup texts
    public string $popupTitle = 'Cookie Settings';
    public string $popupDescription = 'We use cookies to enhance your browsing experience and analyze site traffic. Please choose your cookie preferences below.';
    public string $acceptAllLabel = 'Accept All';
    public string $rejectAllLabel = 'Reject All';
    public string $savePreferencesLabel = 'Save Preferences';
    public string $cookiePolicyUrl = '';

    // Appearance
    public string $popupLayout = 'bar'; // bar, box, modal
    public string $popupPosition = 'bottom'; // bottom, top, center
    public string $primaryColor = '#2563eb';
    public string $backgroundColor = '#ffffff';
    public string $textColor = '#1f2937';
    public string $overlayEnabled = 'false';

    // Behaviour
    public string $autoShowPopup = 'true';
    public string $consentExpiry = '365'; // days
    public string $logConsent = 'true';

    public function defineRules(): array
    {
        return [
            [['popupTitle', 'acceptAllLabel', 'rejectAllLabel', 'savePreferencesLabel'], 'required'],
            [['popupLayout'], 'in', 'range' => ['bar', 'box', 'modal']],
            [['popupPosition'], 'in', 'range' => ['bottom', 'top', 'center']],
        ];
    }
}
