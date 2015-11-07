<?php
/**

 * Author: Arne Gockeln, WebSDK
 * Date: 31.08.15
 */

namespace WebSDK;


class Pagination
{
    private $mysql;
    private $rows_per_page = 25;
    private $total_pages = 0;
    private $total_rows = 0;
    private $current_page = 0;
    private $baseRoute = '';

    /**
     * Constructor
     * @param Database $dbInstance Database Instance
     * @param int $current_page current page id
     * @param int $rows_per_page number of total rows per page
     */
    public function __construct(&$dbInstance, $current_page = 0, $rows_per_page = 25){
        $this->reset();
        $this->setMysql($dbInstance);
        $this->setRowsPerPage($rows_per_page);
        $this->setCurrentPage($current_page);
        $this->run();
    }

    /**
     * Run pagination
     */
    private function run(){
        // get total rows
        $this->setTotalRows($this->getMysql()->getNumRows());
        // get total pages
        $this->setTotalPages(ceil($this->getTotalRows() / $this->getRowsPerPage()));
        // modify sql string
        $sql = $this->getMysql()->getSqlString();
        $sql .= " LIMIT ". $this->getCurrentPage() * $this->getRowsPerPage() . ", " . $this->getRowsPerPage();
        $this->getMysql()->query($sql);
    }

    /**
     * Reset pagination object to defaults
     */
    public function reset(){
        $this->setMysql('');
        $this->setBaseRoute('');
        $this->setRowsPerPage(25);
        $this->setTotalPages(0);
        $this->setTotalRows(0);
        $this->setCurrentPage(0);
    }

    /**
     * Get pagination array for templating
     * @return array
     */
    public function toArray(){
        return array(
            'rows_per_page' => $this->getRowsPerPage(),
            'total_pages' => $this->getTotalPages(),
            'total_rows' => $this->getTotalRows(),
            'current_page' => $this->getCurrentPage(),
            'next_page' => $this->getNextPage(),
            'next_page_url' => $this->getNextPageUrl(),
            'previous_page' => $this->getPreviousPage(),
            'previous_page_url' => $this->getPreviousPageUrl(),
            'pages' => $this->getPages()
        );
    }

    /**
     * Get next page url
     * @param int $id
     * @return string
     */
    public function getNextPageUrl($id = -1){
        if(($id > -1 || $this->getNextPage() > -1)){
            return str_replace('%s', ($id > -1 ? $id : $this->getNextPage()), $this->getBaseRoute());
        }
        return '';
    }

    /**
     * Get previous page url
     * @param int $id
     * @return string
     */
    public function getPreviousPageUrl($id = -1){
        if(($id > -1 || $this->getPreviousPage() > -1)){
            return str_replace('%s', ($id > -1 ? $id : $this->getPreviousPage()), $this->getBaseRoute());
        }
        return '';
    }

    /**
     * Get pages
     * @return array
     */
    public function getPages(){
        $return = array();
        if($this->getTotalRows() > $this->getRowsPerPage()){
            for($i = 0; $i < $this->getTotalPages(); $i++){
                $return[$this->getNextPageUrl($i)] = $i+1;
            }
        }
        return $return;
    }

    /**
     * Get next page id or -1 if not available
     * @return int
     */
    public function getNextPage(){
        return ($this->getCurrentPage()+1 < $this->getTotalPages() ? $this->getCurrentPage()+1 : -1);
    }

    /**
     * Get previous page id or -1 if not available
     * @return int
     */
    public function getPreviousPage(){
        return ($this->getCurrentPage() > 0 ? $this->getCurrentPage()-1 : -1);
    }

    /**
     * @return Database
     */
    public function getMysql()
    {
        return $this->mysql;
    }

    /**
     * @param Database $mysql
     */
    public function setMysql($mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * @return int
     */
    public function getRowsPerPage()
    {
        return $this->rows_per_page;
    }

    /**
     * @param int $rows_per_page
     */
    public function setRowsPerPage($rows_per_page)
    {
        $this->rows_per_page = $rows_per_page;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * @param int $total_pages
     */
    public function setTotalPages($total_pages)
    {
        $this->total_pages = $total_pages;
    }

    /**
     * @return int
     */
    public function getTotalRows()
    {
        return $this->total_rows;
    }

    /**
     * @param int $total_rows
     */
    public function setTotalRows($total_rows)
    {
        $this->total_rows = $total_rows;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * @param int $current_page
     */
    public function setCurrentPage($current_page)
    {
        $this->current_page = $current_page;
    }

    /**
     * @return string
     */
    public function getBaseRoute()
    {
        return $this->baseRoute;
    }

    /**
     * @param string $baseRoute
     */
    public function setBaseRoute($baseRoute)
    {
        $this->baseRoute = $baseRoute;
    }
}