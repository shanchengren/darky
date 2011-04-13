/*
* app.insertImage 模块
* 图片插入模块
*/
Breeze.namespace('app.insertImage', function(B) {
	var win = window,doc = document,
	    defaultConfig = {
	        rspHtmlPath:imageConfig.url + (typeof imageConfig.tabs == 'undefined' ? '' : '&tabs=' + imageConfig.tabs),
	        callback:function(){}
	    },
	    
	    imageSelector = {
	        id :'editor-insertImage',
	        load : function(elem, editor) {
				var id = this.id;
				B.require('request','util.dialog', function(B) {
					B.ajax({
	                    url:defaultConfig.rspHtmlPath,
	                    dataType:'html',
	                    cache:false,
						//contentType: "text/html; charset=UTF-8",
	                    success:function(data) {
                            B.util.dialog({
                                pos: ['leftAlign','bottom'],
                                id: id,
                                data: unescape(data),
                                reuse: true,
                                callback: function(popup) {
                                    InsertImage();//事件处理类
									initList();
									editor.area.appendChild(popup.win);//转移弹窗位置
                                }
                            }, elem);
	                    }
	                });
	            });
	        }
	    };
	    
	/**
     * 隐藏面板
     */   
	function hideImageSelector() {
        B.$('#' + imageSelector.id).style.display = 'none';
    }
    //给相册图片添加选中事件
    function addPhotoClick() {
        B.require('event',function() {
            B.$$('#' + imageSelector.id +' #photoList li').forEach(function(n) {
                n['onmousedown'] = function(e){
					insertTrigger('<img src="' + B.$('input', this).value + '" />');
					//hideImageSelector();
					//this.className = this.className == 'current' ? '' :'current'
				}
            });
        });
    }
    /**
     * 获取相册中的相片
     */ 
    function getPhotosByAlbumId(albumId) {
        B.require('request',function(B) {
            B.ajax({
                type:'post',
                url:defaultConfig.rspHtmlPath + '&verify='+window.verifyhash,
                data: {job:'listphotos',aid:albumId},
                success :function(data) {
                    var rText = data.split('\t'),
						albumHtml = '';
					if (rText[0] == 'success') {
						try{
							var photos = B.parseJSON(rText[1]);
							if (typeof(photos)=='object') {
								for (var i in photos) {
									if (typeof(photos[i])=='object') {
										albumHtml += '<li><span></span><label><img src="'+ photos[i].thumbpath +'" width="56" height="56" /><input type="checkbox" value="' + photos[i].path + '" /></label></li>';
									}
								}
								B.$('#album').innerHTML = albumHtml;
								B.$('#photoList').style.display='';
								B.$('#noPhotoList').style.display='none';
							}
						}catch(e){}
						addPhotoClick(B.$('#photoList'));
					}else{
						B.$('#photoList').style.display='none';
						B.$('#noPhotoList').style.display='';

					}
                }
            });
        });
    };
	/**
	 * 初始化列表
	 */
	function initList(){
	}
    /**
     * ImageInsert类
     */   
    function InsertImage(elem) {
        var self = this;
        if( !(self instanceof InsertImage) ) {
		    return new InsertImage();
	    }
        B.require('event',function(B) {//add event for ImageInsert
            var selector = '#'+ imageSelector.id;

			if (B.$(selector + ' #tab_local')) {
				//picuploader
				B.require('global.uploader', function(){
					uploader.init('picuploader');
				});
			}
			if (B.$(selector + ' #tab_network')) {
				//最终点击插入图片按钮事件
				B.$('#btn_insertImg').onclick = function() {
					insertTrigger('<img src="' + B.$('#networkImg').value + '" />');
				};
			}
			if (B.$('#album_list')){
				B.$('#album_list').onchange = function(){
					getPhotosByAlbumId(this.value);
				};
				addPhotoClick();
				/**
				 * 产生相册中图片延迟加载效果
				 */
				B.require('util.lazyload',function(B) {
					B.util.lazyload('img',{
						container: B.$('#photoList')
					});
				});
			}
            /**
             * 产生tab效果
            */
            B.require('util.scrollable',function(B) {
                B.util.tabs("#"+imageSelector.id);
            }); 
            
            /**
             * 切换tab时触发
             */
            B.$$(selector + ' .B_tab_trigger').forEach(function(n) {
                B.addEvent(n, 'click', function(e) {
                    B.$$(selector + ' .B_tab_trigger').forEach(function(n) {
                        B.removeClass(n,'current');
                    });
                    B.addClass(this, 'current');
					if(this.id == 'tab_local'){
						uploader.init('picuploader');
					}
					e.preventDefault();
                });
            });
            //window.verifyhash = B.$('#verifyhash').value;//服务器输出的verifyhash,ajax需要(为兼容PW)
            /**
             * 关闭面板事件
             */
            B.$$(selector + ' .B_menu_adel').forEach(function(n) {
                B.addEvent(n,'click',function(e) {
                    e.preventDefault();
                    hideImageSelector();
                });
            });
        });
	}
	/**
	 * @description 图片选择器
	 * @params {String} 要产生图片选择器的元素
	 * @params {Function} 点击图片后产生的回调函数
	 */
	B.app.insertImage = function(elem, callback, editor) {
	    insertTrigger = callback;
		if (B.$('#'+imageSelector.id)){
			B.util.dialog({
				id:imageSelector.id,
				reuse: true,
				callback:function(){
					if (B.$('#' + imageSelector.id + ' #tab_local') && B.hasClass(B.$('#tab_local'), 'current')) {
						uploader.init('picuploader');
					}
				},
				pos: ['leftAlign', 'bottom']
			}, elem);
		} else {
			imageSelector.load(elem, editor);
		}
    }
});