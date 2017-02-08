function execute_complete(jqXHR)
{
    var data = jqXHR.responseJSON;
    if(data.overall == true)
    {
        $("#pending_table").dataTable().api().ajax.reload();
    }
    else
    {
        alert("One or more delete operation failed");
    }
}

(function($){

  /**
 *    * $.invoke(elements, func, context, extend)
 *       *
 *          * @param {array|object} list of elements to iterate
 *             * @param {function} function with arguments(element, name)
 *                * @param {context} applied this; default: current element
 *                   * @param {boolean} extend each element as jQuery object
 *                      */
  $.invoke = function(elements, func, context, extend){
    return $.each(elements, function(name, elem){
      func.apply(context || this, [extend ? $(elem) : elem, name]);
    });
  };

  /**
 *    * $('elements').invoke(function(elem, name){}, context)
 *       *
 *          * @param {function} function with arguments(element, name)
 *             * @param {context} applied this; default: current element
 *                */
  $.fn.invoke = function(func, context){
    return $.invoke(this, func, context, true);
  };

})(jQuery);

function execute_operation(element)
{
    var data = $("#pending_table").DataTable().row(element).data();
    $.ajax({
        url: '../api/v1/pending_users/'+data.hash+this.url_extended,
        type: this.method,
        dataType: 'json',
        complete: execute_complete
    })
}

function pendingExecute()
{
    var context = {};
    context.url_extended = '';
    switch($("#pending_action")[0].value)
    {
        case "none":
            return;
        case "del":
            context.method = 'DELETE';
            break;
    }
    var selected_rows = $("#pending_table").DataTable().rows('.selected');
    if(selected_rows.length < 1)
    {
        return;
    }
    $.invoke(selected_rows[0], execute_operation, context);
}

function onPendingTableBodyClick()
{
    if($(this).hasClass('selected')) 
    {
        $(this).removeClass('selected');
    }
    else 
    {
        $(this).addClass('selected');
    }
}

function tableDrawComplete()
{
    if($("#pending_table").dataTable().api().data().length == 0)
    {
        $("#pending_set").empty();
        $("#pending_set").append("No Pending Registrations at this time.");
    }
}

function do_pending_init()
{
    if($("#pending_table").length > 0)
    {
        $("#pending_table").DataTable({
            'ajax': '../api/v1/pending_users?fmt=data-table',
            'columns': [
                {'data':'uid'},
                {'data':'mail'},
                {'data':'time'}
            ]
        });

        $("#pending_table tbody").on('click', 'tr', onPendingTableBodyClick);

        $("#pending_table").on('draw.dt', tableDrawComplete);
    }
}

$(do_pending_init);
