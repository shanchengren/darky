/*
* app.insertAttach 模块
* 附件插入模块
*/
Breeze.namespace('app.insertAttach', function (B) {
	//flash控件
	var $ = B.query, index = 1;
    var win = window, doc = document,
        defaultConfig = {
            rspHtmlPath: attachConfig.url,
            callback: function () { }
        },
        tattachSelector = {
            id: 'editor-insertTattach',
            load: function (elem, editor) {
                var id = this.id;
                B.require('request', 'util.dialog', function (B) {
					B.ajax({
                        url: defaultConfig.rspHtmlPath,
                        dataType: 'html',
                        cache: false,
                        success: function (data) {
                            B.util.dialog({
                                pos: ['leftAlign', 'bottom'],
                                id: id,
                                data: data,
                                reuse: true,
                                callback: function (popup) {
									B.require('global.uploader',function(){
										uploader.init('uploader');
										if ( B.data(elem, 'mutiupload') ){
											B.query('.B_tips').css('display', 'block');
										}
										B.addEvent(B.$('#B_sm_cfg'), 'click', uploader.toggleSelect);
									});
									editor.area.appendChild(popup.win);
                                }
                            }, elem);
                        },
                        error: function (data) {
                            alert(data);
                        }
                    });
                });
            }
        }

    /**
    * @description 图片选择器
    * @params {String} 要产生附件选择器的元素
    * @params {Function} 选择附件后产生的回调函数
    */
   B.app.insertAttach = function (elem, callback, editor) {
        insertTrigger = callback;
		if (B.$('#'+tattachSelector.id)){
			B.util.dialog({
				id: tattachSelector.id,
				reuse: true,
				callback:function(){uploader.init('uploader');},
				pos: ['leftAlign', 'bottom']
			}, elem);
		} else {
			tattachSelector.load(elem, editor);
		}
    }
});


/*
此组件涉及到先通过ajax加载HTML,所以事件处理类InsertAttach在tattachSelector.load()中实例化,这与colorpicker有点不同,分开来html和event为了更容易维护和阅读
*/