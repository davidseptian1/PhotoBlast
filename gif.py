import os
import argparse
from PIL import Image
from moviepy.editor import ImageSequenceClip, concatenate_videoclips

def add_white_border(img, border_size=50):
    """Add white border to an image"""
    width, height = img.size
    new_width = width + (2 * border_size)
    new_height = height + (2 * border_size)
    new_img = Image.new('RGB', (new_width, new_height), 'white')
    new_img.paste(img, (border_size, border_size))
    return new_img

def add_frame_and_crop(photo, frame, frame_width, frame_height, crop_width, crop_height):
    """Add frame and crop the image"""
    if frame.mode != 'RGBA':
        frame = frame.convert('RGBA')

    frame_resized = frame.resize((frame_width, frame_height), Image.Resampling.LANCZOS)
    result = Image.new('RGBA', (frame_width, frame_height), (0, 0, 0, 0))

    x = (frame_width - photo.size[0]) // 2
    y = (frame_height - photo.size[1]) // 2

    result.paste(photo, (x, y))
    result = Image.alpha_composite(result, frame_resized)

    left = (frame_width - crop_width) // 2
    top = (frame_height - crop_height) // 2
    right = left + crop_width
    bottom = top + crop_height

    return result.crop((left, top, right, bottom))

def create_slideshow(input_dir, frame_path, output_video, fps=2, repeat=6):
    """
    Create a high quality slideshow video from photos

    Parameters:
    - input_dir: directory containing input photos
    - frame_path: path to the frame image
    - output_video: path for the output video
    - fps: frames per second (default: 0.3 = ~3.3 seconds per frame)
    - repeat: number of times to repeat the slideshow
    """
    # Parameters
    border_size = 55
    frame_width = 640
    frame_height = 580
    crop_width = 620
    crop_height = 540

    # Load frame image
    frame = Image.open(frame_path)

    # Create temporary directory for processed images
    temp_dir = "temp_processed"
    os.makedirs(temp_dir, exist_ok=True)

    # Get all PNG files from input directory
    photo_files = [f for f in os.listdir(input_dir) if f.endswith('.png')]
    photo_files.sort()  # Sort files to ensure consistent order

    processed_images = []

    # Process each photo
    for i, photo_file in enumerate(photo_files):
        # Open and process image
        img = Image.open(os.path.join(input_dir, photo_file))

        # Add white border
        img_with_border = add_white_border(img, border_size)

        # Add frame and crop
        final_img = add_frame_and_crop(
            img_with_border, frame,
            frame_width, frame_height,
            crop_width, crop_height
        )

        # Save processed image with high quality
        temp_path = os.path.join(temp_dir, f"processed_{i}.png")
        final_img.convert('RGB').save(temp_path, 'PNG', quality=100, optimize=False)
        processed_images.append(temp_path)

    # Create video clip
    clip = ImageSequenceClip(processed_images, fps=fps)

    # Repeat the clip
    final_clip = concatenate_videoclips([clip] * repeat)

    # Write high quality video
    final_clip.write_videofile(
        output_video,
        fps=24,  # Higher FPS for smoother transitions
        codec='libx264',
        bitrate="8000k",
        audio=False,  # No audio needed
        preset='veryslow',  # Highest quality encoding
        threads=4  # Use multiple CPU threads
    )

    # Clean up temporary files
    for img_path in processed_images:
        os.remove(img_path)
    os.rmdir(temp_dir)

def main():
    parser = argparse.ArgumentParser(description="Create a slideshow video from photos with a frame and cropping.")
    parser.add_argument('--input_dir', type=str, required=True, help="Directory containing input photos.")
    parser.add_argument('--frame_path', type=str, required=True, help="Path to the frame image.")
    parser.add_argument('--output_video', type=str, required=True, help="Path for the output video.")
    parser.add_argument('--fps', type=float, default=2, help="Frames per second for the slideshow (default: 2).")
    parser.add_argument('--repeat', type=int, default=6, help="Number of times to repeat the slideshow (default: 6).")

    args = parser.parse_args()

    create_slideshow(
        input_dir=args.input_dir,
        frame_path=args.frame_path,
        output_video=args.output_video,
        fps=args.fps,
        repeat=args.repeat
    )

if __name__ == "__main__":
    main()
