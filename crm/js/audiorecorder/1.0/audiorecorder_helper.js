/* 
 *  Этот файл является частью программы "CRM Руководитель" - конструктор CRM систем для бизнеса
 *  https://www.rukovoditel.net.ru/
 *  
 *  CRM Руководитель - это свободное программное обеспечение, 
 *  распространяемое на условиях GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 *  
 *  Автор и правообладатель программы: Харчишина Ольга Александровна (RU), Харчишин Сергей Васильевич (RU).
 *  Государственная регистрация программы для ЭВМ: 2023664624
 *  https://fips.ru/EGD/3b18c104-1db7-4f2d-83fb-2d38e1474ca3
 */

/*
 * The MediaRecorder interface of the MediaStream Recording API provides functionality to easily record media.
 * https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder
 */

class audiorecorder_helper
{
    constructor()
    {        
        this._stream = false;   //stream from getUserMedia()
        this.mediaRecorder = false;        
        this.is_pause = false;
        this.chunks = [];
                
        this.audio_data = new Map();
        this.audio_data_time = new Map();

        this.recordButton = $('#recordButton');
        this.stopButton = $('#stopButton');
        this.pauseButton = $('#pauseButton');
        this.resumeButton = $('#resumeButton');

        
        //add events to those  buttons
        this.recordButton.click(()=>{
            this.startRecording()
        })
        
        this.stopButton.click(()=>{
            this.stopRecording()
        })
        
        this.pauseButton.click(()=>{
            this.pauseRecording()
        })
        
        this.resumeButton.click(()=>{
            this.pauseRecording()
        })        
       
        //slider
        this.setSlider()
        
        //timer        
        this.timerHours = 0;
        this.timerMinutes = 0;
        this.timerSeconds = 0;
                
        this.checkMicrophoneAccess()
    }
    
    checkMicrophoneAccess()
    {        
        navigator.mediaDevices.getUserMedia({audio: true, video: false}).then(stream => {
            this._stream = stream;    
        }).catch(function (err)
        {
            alert(err)
        })               
    }
    
    //stop access to microphone
    stopMicrophoneAccess()
    {
        this._stream.getAudioTracks().forEach(track => {
            track.stop();            
        });
    }
    
    //prepare slider
    setSlider()
    {
        //get recorder length
        this.audio_recording_length = $("#range_slider").attr('audio_recording_length')*60; 
        
        //appli slicder
        $(".audiorecording-slider").ionRangeSlider({
            skin: "flat",
            min: 0,
            max: this.audio_recording_length,
            grid: true,
            prettify: function(n){
                let m = parseInt(n / 60, 10)
                let s = parseInt(n % 60, 10);
                if(m<10) m = '0'+m
                if(s<10) s = '0'+s
                return m+':'+s
            }
        });

        //set current slider
        this.audiorecording_slider = $(".audiorecording-slider").data("ionRangeSlider");
    }
    
    //reset timer
    resetRecordingTimer()
    {                        
        $('#timerHours').html('00:');
        $('#timerMinutes').html('00:');
        $('#timerSeconds').html('00');

        clearTimeout(this.recordingTimer);

        this.audiorecording_slider.update({
            from: 0
        })
        
        this.stopMicrophoneAccess()                
    }
    
    //start timer
    startRecordingTimer()
    {
        this.recordingTimer = setTimeout(()=>
        {                        
            if(((this.timerMinutes*60)+this.timerSeconds)>=this.audio_recording_length)
            {                               
               this.stopRecording() 
               return false;
            }

            this.timerSeconds++;
            if (this.timerSeconds > 59)
            {
                this.timerSeconds = 0;
                this.timerMinutes++;
                if (this.timerMinutes > 59)
                {
                    this.timerMinutes = 0;
                    this.timerHours++;
                    if (this.timerHours < 10)
                    {
                        $("#timerHours").text('0' + this.timerHours + ':')
                    }
                    else
                    {                   
                        $("#timerHours").text(this.timerHours + ':');
                    }
                }

                if (this.timerMinutes < 10)
                {
                    $("#timerMinutes").text('0' + this.timerMinutes + ':');
                }
                else
                {               
                    $("#timerMinutes").text(this.timerMinutes + ':');
                }
            }
            if (this.timerSeconds < 10)
            {
                $("#timerSeconds").text('0' + this.timerSeconds);
            }
            else
            {
                $("#timerSeconds").text(this.timerSeconds);
            }

            this.audiorecording_slider.update({
                from: (this.timerMinutes*60)+this.timerSeconds
            })                       

            this.startRecordingTimer();
        }, 1000);
    }
    
    //start recording
    startRecording()
    {
        //apply timer
        this.timerHours = 0;      
        this.timerMinutes = 0;      
        this.timerSeconds = 0;
        this.startRecordingTimer()
        
        //show buttons
        this.recordButton.addClass('hidden')
        this.stopButton.removeClass('btn-default').removeClass('hidden').addClass('btn-danger')
        this.pauseButton.removeClass('hidden')
        
        //Simple constraints object, for more advanced audio features see                    
        var constraints = {audio: true, video: false}
        
        /*
        We're using the standard promise based getUserMedia() 
        https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
        */
                     
        //var that = this
        navigator.mediaDevices.getUserMedia(constraints).then(stream =>
        {        
            this._stream = stream;
            
            this.mediaRecorder = new MediaRecorder(stream);
            
            this.mediaRecorder.start()   
            
            this.mediaRecorder.ondataavailable = (e) => {
                this.chunks.push(e.data);
            };

        }).catch(err=>
        {
            alert(err)
            
            //disable the record buttons if getUserMedia() fails            
            this.recordButton.removeClass('hidden')
            this.resumeButton.addClass('hidden');
            this.pauseButton.addClass('hidden');
            this.stopButton.addClass('hidden');

            this.resetRecordingTimer()
            
            this.timerHours = 0;      
            this.timerMinutes = 0;      
            this.timerSeconds = 0;
        });
                    
    }
    
    pauseRecording()
    {
        //console.log("pauseButton clicked rec.recording=", rec.recording);
        if (!this.is_pause)
        {
            this.is_pause=true
            //pause
            this.mediaRecorder.pause();
            this.pauseButton.addClass('hidden')
            this.resumeButton.removeClass('hidden')
            
            clearTimeout(this.recordingTimer);
        }
        else
        {
            this.is_pause=false;
            //resume
            this.mediaRecorder.resume()
            
            this.pauseButton.removeClass('hidden')
            this.resumeButton.addClass('hidden')
            
            this.startRecordingTimer()
        } 
    }
    
    stopRecording()
    {
        this.resetRecordingTimer()
        
        this.recordButton.removeClass('hidden')
        this.resumeButton.addClass('hidden');
        this.pauseButton.addClass('hidden');
        this.stopButton.addClass('hidden');
        
         //tell the recorder to stop the recording
        this.mediaRecorder.stop();

        this.stopMicrophoneAccess()
        
        //create the wav blob and pass it on to createDownloadLink
        this.mediaRecorder.onstop = (e) => {
            let blob = new Blob(this.chunks, { type: "audio/ogg; codecs=opus" });            
            this.chunks=[]
            
            this.createDownloadLink(blob)
        }
    }
    
    //create links to preview audio
    createDownloadLink(blob)
    {
        //console.log(blob)
        //webkitURL is deprecated but nevertheless
        let URL = window.URL || window.webkitURL;
        
        let url = URL.createObjectURL(blob);
        let au = document.createElement('audio');
        let li = document.createElement('li');        
        let remove_link = document.createElement('a');
        let link_id = 'auido_' + random_value(6);

        li.id = link_id;
        
        //add controls to the <audio> element
        au.controls = true;
        au.setAttribute("controls", "");
        au.src = url;
        au.classList.add('audiorecording-preview');        

        //add the new audio element to li
        li.appendChild(au);

        this.audio_data.set(link_id,blob)
        this.audio_data_time.set(link_id,(this.timerMinutes<10 ? '0'+this.timerMinutes : this.timerMinutes)+'.'+(this.timerSeconds<10 ? '0'+this.timerSeconds : this.timerSeconds))

        //console.log(this.audio_data_time)

        //add remove link
        remove_link.innerHTML='<i class="fa fa-trash-o"></i>'
        remove_link.classList.add('btn');
        remove_link.classList.add('btn-default');
        remove_link.style.cssText = "float:right"

        remove_link.addEventListener('click',()=>{
            $('#'+link_id).remove() 

            this.audio_data.delete(link_id)
            this.audio_data_time.delete(link_id)

            this.showSaveButton()
        })

        li.appendChild(remove_link)

        //add the li element to the ol
        recordingsList.appendChild(li);

        this.showSaveButton()
        
        this.timerHours = 0;
        this.timerMinutes = 0;
        this.timerSeconds = 0;
    }
    
    //show or hide save button
    showSaveButton()
    {        
        if($('#recordingsList li').length>0)
        {
            $('#recordSave').removeClass('hidden')
        }
        else
        {
            $('#recordSave').addClass('hidden')
        }
    }    
}

