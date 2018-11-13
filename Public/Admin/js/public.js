/**
 * 公共js方法文件
 * Created by Mrx on 2016/4/18.
 */

function del_confirm()
{
    var r=confirm("确定要删除吗，删除之后将不可恢复!");
    if (r)
    {
        return true;
    }else {
        return false;
    }
}
