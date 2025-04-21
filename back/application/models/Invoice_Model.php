<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_Model extends LPTF_Model
{
    private $table = 'invoice';

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('API_Helper');
        $this->api_helper = new API_Helper($this->db, $this->table);
    }

    public function getInvoice($params)
    {
        $constraints = [
            ['id', 'optional', 'number'],
            ['name', 'optional', 'string'],
            ['reference', 'optional', 'string'],
            ['link', 'optional', 'string'],
            ['author', 'optional', 'string'],
            ['creation_date', 'optional', 'string'],
            ['amount', 'optional', 'number'],
            ['hours', 'optional', 'number'],
            ['alternance_id', 'optional', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getInvoiceFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->get($this->table);

        $Invoices_arr = $query->result_array();


        return ($Invoices_arr);
    }

    public function postInvoice($params)
    {
        $constraints = [
            ['file', 'mandatory', 'string'],
            ['name', 'mandatory', 'string'],
            ['reference', 'mandatory', 'string'],
            ['fileType', 'mandatory', 'string'],
            ['amount', 'mandatory', 'number'],
            ['hours', 'mandatory', 'number'],
            ['alternance_id', 'mandatory', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $acceptedTypes = ["image/png", "image/jpeg", "image/jpg", "application/pdf"];
        if (!in_array($params['fileType'], $acceptedTypes)) return ($this->Status()->PreconditionFailed());

        $data = base64_decode($params['file']);

        require_once './application/helpers/google_drive_helper.php';
        $GDrive = new Google_Drive_Helper();

        $upload = $GDrive->uploadBasic($data, $params['name'], $params['fileType'], GOOGLE_DRIVE_INVOICE_FOLDER);
        if (!$upload) return ($this->Status()->Error());

        $today = new DateTime();
        $email = $this->token_helper->get_payload()['user_email'];
        if ($email === null) return ($this->Status()->ExpectationFailed());

        $data = [
            'name' => $params['name'],
            'link' => $upload,
            'reference' => $params['reference'],
            'author' => $email,
            'amount' => $params['amount'] * 100,
            'hours' => $params['hours'],
            'alternance_fk' => $params['alternance_id'],
            'creation_date' => $today->format('Y-m-d')
        ];

        $response = $this->db->insert($this->table, $data);

        return ($response === true ? $this->db->insert_id() : false);
    }

    public function putInvoice($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number'],
            ['name', 'optional', 'string'],
            ['link', 'optional', 'string'],
            ['reference', 'optional', 'string'],
            ['author', 'optional', 'string'],
            ['creation_date', 'optional', 'string'],
            ['amount', 'optional', 'number'],
            ['hours', 'optional', 'number'],
            ['alternance_id', 'optional', 'number'],
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $data = [];
        $optional_fields = [
            ['name', 'name'],
            ['link', 'link'],
            ['reference', 'reference'],
            ['author', 'author'],
            ['creation_date', 'creation_date'],
            ['amount' => $params['amount']],
            ['hours' => $params['hours']],
            ['alternance_fk' => $params['alternance_id']],
        ];

        foreach ($optional_fields as $field) {
            if (array_key_exists($field[1], $params)) {
                $data[$field[0]] = $params[$field[1]];
            }
        }

        if (count($data) > 0) {
            $this->db->where('id', $params['id']);
            $response = $this->db->update($this->table, $data);
        } else {
            $response = $this->Status()->NoContent();
        }

        return ($response);
    }

    public function deleteInvoice($params)
    {
        $constraints = [
            ['id', 'mandatory', 'number']
        ];
        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $this->db->where_in('id', $params['id']);
        $response = $this->db->delete($this->table);

        return ($response);
    }

    public function getInvoiceCount($params)
    {
        $constraints = [
            ['reference_like', 'mandatory', 'string']
        ];

        if ($this->api_helper->checkParameters($params, $constraints) == false) {
            return ($this->Status()->PreconditionFailed());
        }

        $fields = $this->getInvoiceFields();
        $asked_fields = $this->api_helper->buildGet($params, $fields);
        $this->api_helper->addLimitAndOffset($params);

        $query = $this->db->count_all_results($this->table);
        return ($query);
    }

    private function getInvoiceFields()
    {
        return ([
            'id' => [
                'type' => 'in',
                'field' => 'id',
                'filter' => 'where'
            ],
            'name' => [
                'type' => 'in',
                'field' => 'name',
                'filter' => 'where'
            ],
            'reference' => [
                'type' => 'in',
                'field' => 'reference',
                'filter' => 'where'
            ],
            'amount' => [
                'type' => 'in',
                'field' => 'amount',
                'filter' => 'where'
            ],
            'hours' => [
                'type' => 'in',
                'field' => 'hours',
                'filter' => 'where'
            ],
            'alternance_id' => [
                'type' => 'in',
                'field' => 'alternance_fk',
                'filter' => 'where'
            ],
            'reference_like' => [
                'type' => 'in',
                'field' => 'reference',
                'filter' => 'like'
            ],
            'link' => [
                'type' => 'in',
                'field' => 'link',
                'filter' => 'where'
            ],
            'author' => [
                'type' => 'in',
                'field' => 'author',
                'filter' => 'where'
            ],
            'creation_date' => [
                'type' => 'in',
                'field' => 'creation_date',
                'filter' => 'where'
            ],
        ]);
    }
}
