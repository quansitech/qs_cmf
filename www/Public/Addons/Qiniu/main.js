$(function() {
    var $initFile = $('#fsUploadProgress').children().length?$('#fsUploadProgress').children():null;
    var uploader = Qiniu.uploader({
        runtimes: 'html5,flash,html4',
        browse_button: 'pickfiles',
        container: 'container',
        drop_element: 'container',
        //max_file_size: '100mb',
        flash_swf_url: 'js/plupload/Moxie.swf',
        dragdrop: true,
        chunk_size: '4mb',
        //uptoken_url: $('#uptoken_url').val(),
        uptoken: $('#uptoken').val(),
        domain: $('#domain').val(),
        auto_start: true,
        init: {
            'FilesAdded': function(up, files) {
                
                $('table').show();
                $('#success').hide();
                
                plupload.each(files, function(file) {
                    var progress = new FileProgress(file, 'fsUploadProgress');
                    progress.setStatus("等待...");
                });
            },
            'BeforeUpload': function(up, file) {
                if($('#fsUploadProgress').children().length>0){
                    $('#fsUploadProgress').children().hide();
                }
                $('#pickfiles').hide();
                var progress = new FileProgress(file, 'fsUploadProgress');
                var chunk_size = plupload.parseSize(this.getOption('chunk_size'));
                if (up.runtime === 'html5' && chunk_size) {
                    progress.setChunkProgess(chunk_size);
                }
            },
            'UploadProgress': function(up, file) {
                var progress = new FileProgress(file, 'fsUploadProgress');
                var chunk_size = plupload.parseSize(this.getOption('chunk_size'));

                progress.setProgress(file.percent + "%", file.speed, chunk_size);
            },
            'UploadComplete': function() {
                $('#pickfiles').show();
                $('#success').show();
            },
            'FileUploaded': function(up, file, info) {
                var size = file['size'];
                var name = file['name'];
                var res = $.parseJSON(info);
                var url = 'http://' + $('#domain').val() + '/' + name; 
                
                $.ajax({
                    cache: true,
                    type: "POST",
                    url: $('#setFilePicUrl').val(),
                    data: "url="+url+"&title="+name+"&ref_id="+res.persistentId+"&size="+size,
                    error: function(request) {
                        alert('获取信息失败！');
                    },
                    success: function(data) {
                        if(data.status == 0){
                            alert(data.info);
                        }
                        else{
                            $('#file_id').val(data.file_id);
                        }
                    }
                });

                var progress = new FileProgress(file, 'fsUploadProgress');
                progress.setComplete(up, info);
            },
            'Error': function(up, err, errTip) {
                $('table').show();
                var progress = new FileProgress(err.file, 'fsUploadProgress');
                progress.setError();
                progress.setStatus(errTip);
            }
        }
    });

    uploader.bind('FileUploaded', function() {
        console.log('hello man,a file is uploaded');
    });

    $('#container').on(
        'dragenter',
        function(e) {
            e.preventDefault();
            $('#container').addClass('draging');
            e.stopPropagation();
        }
    ).on('drop', function(e) {
        e.preventDefault();
        $('#container').removeClass('draging');
        e.stopPropagation();
    }).on('dragleave', function(e) {
        e.preventDefault();
        $('#container').removeClass('draging');
        e.stopPropagation();
    }).on('dragover', function(e) {
        e.preventDefault();
        $('#container').addClass('draging');
        e.stopPropagation();
    });


    $('body').on('click', 'table button.btn', function() {
        $(this).parents('tr').next().toggle();
    });

//    function initPlayer(vLink) {
//
//        if ($("#video-embed").length) {
//            return;
//        }
//
//        var vType = function() {
//
//            var type = '';
//            $.ajax({
//                url: vLink + "?stat",
//                async: false
//            }).done(function(info) {
//                type = info.mimeType;
//                if (type == 'application/x-mpegurl') {
//                    type = 'application/x-mpegURL';
//                }
//            });
//
//            return type;
//        };
//
//        var player = $('<video id="video-embed" class="video-js vjs-default-skin"></video>');
//        $('#video-container').empty();
//        $('#video-container').append(player);
//
//        console.log('=======>>Type:', vType(), '====>>vLink:', vLink);
//        var poster = vLink + '?vframe/jpg/offset/2';
//        videojs('video-embed', {
//            "width": "100%",
//            "height": "500px",
//            "controls": true,
//            "autoplay": false,
//            "preload": "auto",
//            "poster": poster
//        }, function() {
//            this.src({
//                type: vType(),
//                src: vLink
//            });
//        });
//    }

//    function disposePlayer() {
//        if ($("#video-embed").length) {
//            $('#video-container').empty();
//            _V_('video-embed').dispose();
//        }
//    }


//    $('#fsUploadProgress').on('click','.play-btn', function() {
//        //disposePlayer();
//        
//    });
    var player = null;
    $('tbody').on('click', '.play-btn', function() {
        $('#myModal-video').modal();
        var url = $(this).data('url');
        
        if ($("#video_1").length) {
            return;
        }
        
        var vType = function() {

            var type = '';
            $.ajax({
                url: url + "?stat",
                async: false
            }).done(function(info) {
                type = info.mimeType;
                if (type == 'application/x-mpegurl') {
                    type = 'application/x-mpegURL';
                }
            });

            return type;
        };
        
        $('#myModal-video .video-preview').empty();
        $('#myModal-video .video-preview').append('<video id="video_1" class="video-js vjs-default-skin" controls preload="auto" width="640" height="480"></video>');
        
        player = videojs('video_1', {
                techOrder: ['flash', 'html5'],
                autoplay: true,
                sources: [{ 
                        src: url,
                        type: vType()
                }]
        });
        
    });
    
    $('#myModal-video .close').on('click', function(){
        player.dispose();
        
    });
    
    $('#fsUploadProgress').on('click', '.delete_row', function(){
        $('#file_id').val('');
        $(this).parents('tr').remove();
        $('table').hide();
    });

     $('#fsUploadProgress').on('click','.cancel_row', function(){
         //只有单文件上传的业务，取消后清空文件队列
         uploader.files = [];
         uploader.stop();
         $('#pickfiles').show();
         $('#fsUploadProgress').fadeOut(500, function() {
            $(this).empty().show();
            if($initFile != null){
                $(this).append($initFile.html());
            }
            else{
                $(this).parent().hide();
            }
         });
     });
    
    
    
    
});