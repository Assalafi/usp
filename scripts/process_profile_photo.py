#!/usr/bin/env python3
"""Validate and prepare a clear, background-cleaned shoulder portrait."""
import argparse, os, sys
from io import BytesIO

try:
    import cv2
    import numpy as np
    from PIL import Image, ImageOps
    from rembg import new_session, remove
except Exception as exc:
    print("Photo processing is not available on the server: %s" % exc, file=sys.stderr)
    sys.exit(2)

def iou(first, second):
    ax, ay, aw, ah = first; bx, by, bw, bh = second
    left, top = max(ax, bx), max(ay, by); right, bottom = min(ax + aw, bx + bw), min(ay + ah, by + bh)
    intersection = max(0, right - left) * max(0, bottom - top)
    union = aw * ah + bw * bh - intersection
    return intersection / union if union else 0

def unique_faces(faces):
    ordered = sorted([tuple(int(value) for value in face) for face in faces], key=lambda item: item[2] * item[3], reverse=True)
    result = []
    for face in ordered:
        # Haar cascades often return several slightly shifted boxes for one face.
        # Treat boxes with strong overlap or a nearly identical centre as one face.
        fx, fy, fw, fh = face
        fcx, fcy = fx + fw / 2.0, fy + fh / 2.0
        duplicate = False
        for existing in result:
            ex, ey, ew, eh = existing
            ecx, ecy = ex + ew / 2.0, ey + eh / 2.0
            size_ratio = min(fw * fh, ew * eh) / max(fw * fh, ew * eh)
            centre_distance = ((fcx - ecx) ** 2 + (fcy - ecy) ** 2) ** 0.5
            duplicate = iou(face, existing) >= 0.45 or (size_ratio >= 0.55 and centre_distance <= max(fw, fh, ew, eh) * 0.30)
            if duplicate:
                break
        if not duplicate:
            result.append(face)
    return result

def detect_single_face(bgr):
    candidates = ['/usr/share/opencv4/haarcascades/haarcascade_frontalface_default.xml', '/usr/share/opencv/haarcascades/haarcascade_frontalface_default.xml']
    if getattr(getattr(cv2, 'data', None), 'haarcascades', None): candidates.insert(0, os.path.join(cv2.data.haarcascades, 'haarcascade_frontalface_default.xml'))
    path = next((candidate for candidate in candidates if os.path.isfile(candidate)), '')
    if not path: raise ValueError('Face detection is not configured on the server.')
    detector = cv2.CascadeClassifier(path)
    gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)
    faces = unique_faces(detector.detectMultiScale(gray, scaleFactor=1.08, minNeighbors=6, minSize=(50, 50)))
    if not faces: faces = unique_faces(detector.detectMultiScale(cv2.equalizeHist(gray), scaleFactor=1.05, minNeighbors=6, minSize=(50, 50)))
    # Very small detections are usually texture/noise. Keep a second face only
    # when it is substantial relative to the main detected face.
    if len(faces) > 1:
        largest_area = max(width * height for _, _, width, height in faces)
        faces = [face for face in faces if face[2] * face[3] >= largest_area * 0.30]
    if len(faces) == 0: raise ValueError('No face was detected. Upload a clear, front-facing passport photograph.')
    if len(faces) > 1: raise ValueError('Multiple faces were detected. Upload a photo containing only the student.')
    return faces[0]

def shoulder_crop(image, alpha, face):
    height, width = image.shape[:2]; x, y, face_width, face_height = face
    crop_size = min(width, height, max(360, int(face_width * 2.05)))
    subject_mask = (alpha > 160).astype(np.uint8)
    components, labels, stats, _ = cv2.connectedComponentsWithStats(subject_mask, 8)
    subject_top = y
    if components > 1:
        largest = 1 + int(np.argmax(stats[1:, cv2.CC_STAT_AREA])); points = np.column_stack(np.where(labels == largest))
        if len(points): subject_top = int(points[:, 0].min())
    center_x = x + face_width / 2; left = int(round(center_x - crop_size / 2)); top = int(round(subject_top - crop_size * 0.025))
    left = max(0, min(left, width - crop_size)); top = max(0, min(top, height - crop_size))
    return image[top:top + crop_size, left:left + crop_size], alpha[top:top + crop_size, left:left + crop_size]

def clean_background(crop, alpha):
    height, width = crop.shape[:2]
    if float(np.count_nonzero(alpha > 160)) / float(width * height) < 0.08: raise ValueError('The subject could not be isolated. Upload a photo with a simple background.')
    binary = (alpha > 160).astype(np.uint8); components, labels, stats, _ = cv2.connectedComponentsWithStats(binary, 8)
    if components > 1:
        largest = 1 + int(np.argmax(stats[1:, cv2.CC_STAT_AREA])); alpha = np.where(labels == largest, alpha, 0).astype(np.uint8)
    alpha = cv2.GaussianBlur(alpha, (3, 3), 0).astype(np.float32) / 255.0; alpha[alpha < 0.08] = 0
    white = np.full_like(crop, 255)
    return (crop.astype(np.float32) * alpha[:, :, None] + white.astype(np.float32) * (1 - alpha[:, :, None])).astype(np.uint8)

def encode_under_limit(image, output_path, max_bytes):
    target = image
    for _ in range(7):
        for quality in (94, 90, 86, 82, 78, 72, 66, 58, 50, 42):
            ok, encoded = cv2.imencode('.jpg', target, [cv2.IMWRITE_JPEG_QUALITY, quality, cv2.IMWRITE_JPEG_OPTIMIZE, 1])
            if ok and len(encoded) <= max_bytes:
                os.makedirs(os.path.dirname(output_path), exist_ok=True); open(output_path, 'wb').write(encoded.tobytes()); return len(encoded)
        target = cv2.resize(target, (max(240, int(target.shape[1] * .85)), max(320, int(target.shape[0] * .85))), interpolation=cv2.INTER_AREA)
    raise ValueError('The image could not be compressed below 200 KB without losing reasonable clarity.')

def main():
    parser = argparse.ArgumentParser(); parser.add_argument('--input', required=True); parser.add_argument('--output', required=True); parser.add_argument('--max-bytes', type=int, default=204800); args = parser.parse_args()
    try:
        with Image.open(args.input) as source: image = ImageOps.exif_transpose(source).convert('RGB')
        image.thumbnail((2400, 2400), Image.Resampling.LANCZOS); rgb = np.array(image); face = detect_single_face(cv2.cvtColor(rgb, cv2.COLOR_RGB2BGR))
        prepared = BytesIO(); image.save(prepared, format='PNG'); removed = remove(prepared.getvalue(), session=new_session('isnet-general-use'), alpha_matting=False)
        rgba = np.array(Image.open(BytesIO(removed)).convert('RGBA')); crop, crop_alpha = shoulder_crop(cv2.cvtColor(rgba[:, :, :3], cv2.COLOR_RGB2BGR), rgba[:, :, 3], face)
        size = encode_under_limit(clean_background(crop, crop_alpha), args.output, args.max_bytes); print('{"bytes": %d, "faces": 1}' % size); return 0
    except (OSError, ValueError, cv2.error) as exc:
        print(str(exc), file=sys.stderr); return 2

if __name__ == '__main__': sys.exit(main())
