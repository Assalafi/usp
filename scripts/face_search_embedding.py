#!/usr/bin/env python3
"""Create a normalized face embedding with OpenCV YuNet + SFace.

This is deliberately separate from the passport-photo beautification script.  The
cleaned/cropped display image can change pixels; face matching should use an
aligned face crop from the original image instead.
"""
import argparse
import json
import os
import sys


def fail(message):
    print(message, file=sys.stderr)
    return 2


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--model-dir', required=True)
    args = parser.parse_args()

    try:
        import cv2
        import numpy as np
    except Exception as exc:
        return fail('Face search is not configured on the server: %s' % exc)

    detector_path = os.path.join(args.model_dir, 'face_detection_yunet_2023mar.onnx')
    recognizer_path = os.path.join(args.model_dir, 'face_recognition_sface_2021dec.onnx')
    if not os.path.isfile(detector_path) or not os.path.isfile(recognizer_path):
        return fail('Face search models are not installed. Ask the system administrator to install the face-search models.')
    if not hasattr(cv2, 'FaceDetectorYN_create') or not hasattr(cv2, 'FaceRecognizerSF_create'):
        return fail('The server needs opencv-contrib-python-headless for accurate face search.')

    image = cv2.imread(args.input, cv2.IMREAD_COLOR)
    if image is None:
        return fail('The captured image could not be read. Please choose a clear JPG or PNG image.')

    height, width = image.shape[:2]
    # Keep detector work bounded for large camera images without changing the
    # identity features used by SFace.
    if max(height, width) > 1800:
        scale = 1800.0 / max(height, width)
        image = cv2.resize(image, (max(1, int(width * scale)), max(1, int(height * scale))), interpolation=cv2.INTER_AREA)
        height, width = image.shape[:2]

    detector = cv2.FaceDetectorYN_create(detector_path, '', (width, height), 0.82, 0.30, 5000)
    _, detected = detector.detect(image)
    faces = [] if detected is None else list(detected)
    faces.sort(key=lambda row: float(row[14]) if len(row) > 14 else 0.0, reverse=True)
    if not faces:
        return fail('No face was detected. Upload a clear, front-facing photo.')

    # YuNet applies NMS, but reject a real second face while tolerating tiny
    # background detections.  A second face must be both reasonably confident
    # and at least 18% of the primary face area.
    primary = faces[0]
    px, py, pw, ph = [float(value) for value in primary[:4]]
    primary_area = max(1.0, pw * ph)
    substantial = []
    for row in faces[1:]:
        x, y, w, h = [float(value) for value in row[:4]]
        area_ratio = (w * h) / primary_area
        confidence = float(row[14]) if len(row) > 14 else 0.0
        if confidence >= 0.55 and area_ratio >= 0.18:
            substantial.append(row)
    if substantial:
        return fail('More than one person was detected. Upload a photo containing only the staff member or student.')

    recognizer = cv2.FaceRecognizerSF_create(recognizer_path, '')
    aligned = recognizer.alignCrop(image, primary)
    feature = recognizer.feature(aligned).reshape(-1).astype(np.float32)
    norm = float(np.linalg.norm(feature))
    if norm <= 1e-8:
        return fail('The face features could not be read. Please use a sharper, front-facing photo.')
    feature /= norm

    face_area_ratio = primary_area / float(max(1, width * height))
    quality = min(1.0, max(0.0, (float(primary[14]) * 0.65) + min(1.0, face_area_ratio * 18.0) * 0.35))
    print(json.dumps({
        'embedding': [float(value) for value in feature],
        'quality': round(quality, 4),
        'faces': 1,
        'detector_score': round(float(primary[14]), 4),
    }))
    return 0


if __name__ == '__main__':
    sys.exit(main())
