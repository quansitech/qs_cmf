namespace Common\Model;

//自动生成代码
class {$table_name}Model extends \Gy_Library\GyListModel{

    protected $_validate = array(
     <volist name='columns' id='col'>
         <eq name="col.require" value="1">
        array('{$col.name}', 'require', '{$col.comment}必填'),
        </eq>
     </volist>
    );

    protected $_auto = array(
        <volist name='add_columns' id='column'>
            <eq name="column.type" value="date">
                array('{$column.name}', 'strtotime', parent::MODEL_BOTH, 'function'),
            <else/>
                <eq name="column.type" value="datetime">
                     array('{$column.name}', 'strtotime', parent::MODEL_BOTH, 'function'),
                </eq>
            </eq>
       
        </volist>
    );
}
