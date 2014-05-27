<?php

class Clean_SqlReports_Block_Adminhtml_Report_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_sqlQueryResults;

    public function __construct()
    {
        parent::__construct();
        $this->setId('reportsGrid');
        $this->setDefaultSort('report_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->unsetChild('search_button');
        $this->unsetChild('reset_filter_button');

        return $this;
    }

    /**
     * @return Clean_SqlReports_Model_Report
     */
    protected function _getReport()
    {
        return Mage::registry('current_report');
    }

    /**
     * @author Lee Saferite <lee.saferite@aoe.com>
     * @return Varien_Data_Collection_Db
     */
    protected function _createCollection()
    {
        return $this->_getReport()->getReportCollection();
    }

    protected function _prepareCollection()
    {
        if (isset($this->_collection)) {
            return $this->_collection;
        }

        $collection = $this->_createCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $collection = $this->_createCollection();
        $collection->setPageSize(1);
        
        try {
            $collection->load();
            $items = $collection->getItems();
            if (count($items)) {
                $item = reset($items);
                foreach ($item->getData() as $key => $val) {
                    $this->addColumn(
                        $key,
                        array(
                            'header'   => Mage::helper('core')->__($key),
                            'index'    => $key,
                            'sortable' => true,
                            'filter_condition_callback' => array($this, '_filterColumn')
                        )
                    );
                }
            }
            
            return parent::_prepareColumns();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Invalid database query!');
            $url = Mage::getSingleton('adminhtml/url')->getUrl('*/*/');
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }
        
        return false;
    }

    protected function _prepareGrid()
    {
        if ($this->_prepareColumns()) {
            $this->_prepareMassactionBlock();
            $this->_prepareCollection();
        }
        
        return $this;
    }
    
    protected function _filterColumn($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        
        $index = $column->getIndex();

        $this->getCollection()->getSelect()->where($index . ' LIKE ?', '%' . $value . '%');
    }
}
