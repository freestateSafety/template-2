<?php

namespace App\Http;


use Symfony\Component\HttpFoundation\Response;

class CsvResponse extends Response
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $filename = 'export.csv';

    /**
     * CsvResponse constructor.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     */
    public function __construct($data = [], $status = 200, array $headers = [])
    {
        $headers = array_merge($headers, ['Content-Type' => 'text/csv']);
        parent::__construct('', $status, $headers);
        $this->setData($data);
        $this->updateHeaderFilename();
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return $this
     */
    public function setData(array $data)
    {
        $csv = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($csv, $row);
        }

        rewind($csv);
        $this->data = stream_get_contents($csv);
        $this->setContent($this->data);
        return $this;
    }

    /**
     * @param $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        $this->updateHeaderFilename();
        return $this;
    }

    private function updateHeaderFilename()
    {
        $this->headers->set('Content-Disposition', 'attachment; filename='.$this->filename);
    }
}
