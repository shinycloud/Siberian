<?php

/**
 * Class Firewall_IndexController
 */
class Firewall_IndexController extends Backoffice_Controller_Default
{
    /**
     * Fetch rules
     *
     * @throws Zend_Exception
     */
    public function findallAction()
    {
        $slackWebHook = __get('fw_slack_webhook');
        if (__getConfig('is_demo')) {
            $slackWebHook = 'https://hooks.slack.com/services/DEMO/DEMO/DEMO';
        }

        $payload = [
            'title' => __('Advanced') . ' > ' . __('Firewall'),
            'icon' => 'icofont icofont-ui-fire-wall',
            'fw_clamd' => [
                'type' => (string) __get('fw_clamd_type'),
                'sock' => (string) __get('fw_clamd_sock'),
                'ip' => (string) __get('fw_clamd_ip'),
                'port' => (string) __get('fw_clamd_port'),
            ],
            'fw_slack' => [
                'is_enabled' => (boolean) __get('fw_slack_is_enabled'),
                'webhook' => $slackWebHook,
                'channel' => __get('fw_slack_channel'),
                'username' => __get('fw_slack_username'),
            ],
        ];

        $rules = (new \Firewall_Model_Rule())
            ->findAll(
                [
                    'type' => \Firewall_Model_Rule::FW_TYPE_UPLOAD
                ],
                [
                    'value ASC'
                ]
            );

        $rulesData = [];
        foreach ($rules as $rule) {
            $rulesData[] = [
                'value' => $rule->getValue()
            ];
        }

        $logs = (new \Firewall_Model_Log())
            ->findAll(
                [],
                [
                    'created_at DESC'
                ],
                [
                    'limit' => 50,
                ]
            );

        $logsData = [];
        foreach ($logs as $log) {
            $user = $log->getUser();
            $userData = [
                'id' => '-',
                'email' => '-',
            ];

            if ($user) {
                $userData = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ];
            }

            $logsData[] = [
                'type' => $log->getType(),
                'message' => $log->getMessage(),
                'user' => $userData,
                'date' => datetime_to_format($log->getCreatedAt(), Zend_Date::DATETIME_LONG),
            ];
        }

        $payload['fw_upload_rules'] = $rulesData;
        $payload['fw_logs'] = $logsData;

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deletefwuploadruleAction()
    {
        try {
            if (__getConfig('is_demo')) {
                // Demo version
                throw new Exception(__("You cannot change Firewall settings, it's a demo version."));
            }

            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(__('Missing value'));
            }

            $value = $params['value'];

            $fwRule = (new \Firewall_Model_Rule())->find([
                'type' => \Firewall_Model_Rule::FW_TYPE_UPLOAD,
                'value' => $value
            ]);

            if ($fwRule->getId()) {
                $fwRule->delete();
            }

            $payload = [
                'success' => true,
                'message' => __('Rule have been removed.'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function addfwuploadruleAction()
    {
        try {
            if (__getConfig('is_demo')) {
                // Demo version
                throw new Exception(__("You cannot change Firewall settings, it's a demo version."));
            }

            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(__('Missing value'));
            }

            $value = trim($params['value']);

            if (empty($value)) {
                throw new \Siberian\Exception(__("Extension can't be empty."));
            }

            if (in_array($value, ['php', 'js', 'ico'])) {
                throw new \Siberian\Exception(__("Extension %s is strictly forbidden.", $value));
            }

            $fwRule = (new \Firewall_Model_Rule())->find([
                'type' => \Firewall_Model_Rule::FW_TYPE_UPLOAD,
                'value' => $value
            ]);

            if (!$fwRule->getId()) {
                $fwRule
                    ->setType(\Firewall_Model_Rule::FW_TYPE_UPLOAD)
                    ->setValue($value)
                    ->save();
            }

            $payload = [
                'success' => true,
                'message' => __('Rule have been added.'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function savefwclamdsettingsAction()
    {
        try {
            if (__getConfig('is_demo')) {
                // Demo version
                throw new Exception(__("You cannot change Firewall settings, it's a demo version."));
            }

            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(__('Missing values'));
            }

            $type = $params['fw_clamd_type'];
            $sock = $params['fw_clamd_sock'];
            $ip = $params['fw_clamd_ip'];
            $port = $params['fw_clamd_port'];

            // Saving values
            __set('fw_clamd_type', $type);
            __set('fw_clamd_sock', $sock);
            __set('fw_clamd_ip', $ip);
            __set('fw_clamd_port', $port);

            $payload = [
                'success' => true,
                'message' => __('ClamAV settings have been saved.'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function savefwslacksettingsAction()
    {
        try {
            if (__getConfig('is_demo')) {
                // Demo version
                throw new Exception(__("You cannot change Firewall settings, it's a demo version."));
            }

            $request = $this->getRequest();
            $params = $request->getBodyParams();

            if (empty($params)) {
                throw new \Siberian\Exception(__('Missing values'));
            }

            $is_enabled = $params['fw_slack_is_enabled'];
            $webhook = $params['fw_slack_webhook'];
            $channel = $params['fw_slack_channel'];
            $username = $params['fw_slack_username'];

            // Saving values
            __set('fw_slack_is_enabled', $is_enabled);
            __set('fw_slack_webhook', $webhook);
            __set('fw_slack_channel', $channel);
            __set('fw_slack_username', $username);

            $payload = [
                'success' => true,
                'message' => __('Firewall Slack settings have been saved.'),
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
