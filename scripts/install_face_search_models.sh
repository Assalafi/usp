#!/usr/bin/env bash
set -euo pipefail

MODEL_DIR="${FACE_SEARCH_MODEL_DIR:-$(pwd)/storage/app/ai-models/face-search}"
PYTHON_BIN="${FACE_SEARCH_PYTHON:-/opt/pg-photo-venv/bin/python3}"
mkdir -p "$MODEL_DIR"
curl -fL --retry 3 -o "$MODEL_DIR/face_detection_yunet_2023mar.onnx" \
  "https://media.githubusercontent.com/media/opencv/opencv_zoo/main/models/face_detection_yunet/face_detection_yunet_2023mar.onnx"
curl -fL --retry 3 -o "$MODEL_DIR/face_recognition_sface_2021dec.onnx" \
  "https://media.githubusercontent.com/media/opencv/opencv_zoo/main/models/face_recognition_sface/face_recognition_sface_2021dec.onnx"
"$PYTHON_BIN" -m pip install --upgrade "opencv-contrib-python-headless==4.11.0.86"
echo "Face-search models installed in $MODEL_DIR"
