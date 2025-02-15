<?php

/**
 * Class Push_Backoffice_CertificateController
 */
class Push_Backoffice_CertificateController extends Backoffice_Controller_Default
{
    /**
     *
     */
    public function loadAction()
    {
        $payload = [
            'title' => $this->_('Push Notifications'),
            'icon' => 'fa-comment-o',
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findallAction()
    {
        $payload = [
            'gcm' => [
                'android_key' => Push_Model_Certificate::getAndroidKey(),
                'android_sender_id' => Push_Model_Certificate::getAndroidSenderId(),
            ]
        ];

        $this->_sendJson($payload);
    }

    /**
     * @throws Siberian_Exception
     */
    public function saveAction()
    {
        if (__getConfig('is_demo')) {
            // Demo version
            throw new Siberian_Exception("This is a demo version, these changes can't be saved");
        }

        $request = $this->getRequest();
        $params = $request->getBodyParams();
        try {
            if (!array_key_exists('android_key', $params) ||
                !array_key_exists('android_sender_id', $params)) {
                throw new Siberian_Exception("GCM Settings are missing!");
            }

            // Android key!
            $certificate = (new Push_Model_Certificate())
                ->find('android_key', 'type');
            if (!$certificate->getId()) {
                $certificate->setType('android_key');
            }
            $certificate
                ->setPath($params['android_key'])
                ->save();

            // Android senderId!
            $certificate = (new Push_Model_Certificate())
                ->find('android_sender_id', 'type');
            if (!$certificate->getId()) {
                $certificate->setType('android_sender_id');
            }
            $certificate
                ->setPath($params['android_sender_id'])
                ->save();

            $payload = [
                'success' => true,
                'message' => __('GCM settings saved.')
            ];

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendHtml($payload);
    }

}
