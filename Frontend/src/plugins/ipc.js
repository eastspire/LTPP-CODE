const video_chunks = [];
let screen_stream = null;
let media_recorder = null;
const width = window.screen.width;
const height = window.screen.height;
const frame_rate = 240;
const video_bits_per_second = width * height * frame_rate;
const video_screen_id = 'video_screen';

const showSaveErrorMsg = function (is_force_show = false) {
  if (is_force_show || !video_chunks || !video_chunks?.length) {
    this.$msg({
      type: 'warning',
      message: '系统检测到录屏内容为空！请开启录屏后重试！',
      duration: 1600,
      offset: 80,
    });
    return true;
  }
  return false;
};

const showStartMsg = function () {
  this.$msg({
    type: 'success',
    message: '系统开始录屏！',
    duration: 1600,
    offset: 80,
  });
};

const save = function () {
  if (showSaveErrorMsg.call(this)) {
    return;
  }
  const blob = new Blob(video_chunks, { type: 'video/webm' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  link.href = url;
  link.download = `ltpp-screen-recording-${timestamp}.webm`;
  // 模拟点击下载
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url); // 释放URL对象
  video_chunks.length = 0;
};

const startVideoScreen = async function (source = null) {
  // 确保浏览器支持HTML5的屏幕捕获API
  if (!navigator.mediaDevices.getDisplayMedia) {
    this.$msg({
      type: 'warning',
      message: '系统检测到当前环境不支持录屏！',
      duration: 1600,
      offset: 80,
    });
    return;
  }

  // 定义视频流的约束条件，请求屏幕共享
  const constraints = {
    video: {
      width: {
        ideal: width,
      },
      height: {
        ideal: height,
      },
      frameRate: {
        ideal: frame_rate, // 帧率
      },
      cursor: {
        ideal: 'always', // 捕获鼠标指针
      },
    },
    audio: false, // 不需要音频
  };
  const video =
    document.getElementById(video_screen_id) || document.createElement('video');
  video.setAttribute('id', video_screen_id);
  if (window.bridge && source) {
    navigator.mediaDevices.getDisplayMedia = async (my_constraints) => {
      my_constraints.video = {};
      my_constraints.video.mandatory = {
        chromeMediaSource: 'desktop',
        chromeMediaSourceId: source?.id,
        width: {
          ideal: width,
        },
        height: {
          ideal: height,
        },
        frameRate: {
          ideal: frame_rate, // 帧率
        },
        cursor: {
          ideal: 'always', // 捕获鼠标指针
        },
      };
      return await navigator.mediaDevices.getUserMedia(my_constraints);
    };
  }
  const stream = await navigator.mediaDevices.getDisplayMedia(constraints);
  stream.videoBitsPerSecond = video_bits_per_second;
  video.srcObject = screen_stream = stream;

  showStartMsg.call(this);

  video.onloadedmetadata = () => {
    video.play(); // 播放视频流
    const options = { mimeType: 'video/webm; codecs=vp9' };
    media_recorder = new MediaRecorder(screen_stream, options);
    media_recorder.ondataavailable = (e) => {
      if (e.data.size > 0) {
        video_chunks.push(e.data);
      }
    };
    media_recorder.onstop = () => {
      save.call(this);
    };
    media_recorder.start(); // 开始录制
  };
};

const stopVideoScreen = function () {
  if (screen_stream) {
    try {
      screen_stream?.getTracks()?.forEach((track) => track?.stop());
    } catch (err) {}
    screen_stream = null;
    const video_element = document.getElementById(video_screen_id);
    if (video_element) {
      video.setAttribute('srcObject', null);
    }
  } else {
    showSaveErrorMsg.call(this, true);
  }
};

export default {
  startVideoScreen,
  stopVideoScreen,
};
