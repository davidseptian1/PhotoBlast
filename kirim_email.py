import os
import smtplib
from email.mime.text import MIMEText
from email.mime.image import MIMEImage
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email import encoders
import time
import argparse
from tqdm import tqdm

def attach_file(msg, file_path):
    """
    Helper function to attach any file to email message with progress bar
    """
    with open(file_path, 'rb') as f:
        file_size = os.path.getsize(file_path)

        # For images
        if file_path.lower().endswith(('.png', '.jpg', '.jpeg')):
            attachment = MIMEImage(f.read())
        # For other files (including videos)
        else:
            attachment = MIMEBase('application', 'octet-stream')
            attachment.set_payload(f.read())
            encoders.encode_base64(attachment)

        # Add header
        filename = os.path.basename(file_path)
        attachment.add_header('Content-Disposition', 'attachment', filename=filename)
        msg.attach(attachment)

        # Menggunakan tqdm untuk menunjukkan progres pengiriman file
        with tqdm(total=file_size, unit='B', unit_scale=True, desc=f"Sending {filename}") as pbar:
            while True:
                chunk = f.read(1024)  # Membaca file dalam potongan kecil
                if not chunk:
                    break
                pbar.update(len(chunk))  # Update progres sesuai dengan panjang chunk yang dibaca

def kirim_email_dengan_media(email_pengirim, email_password, penerima, template, subject, photo_dir, collage_path, video_path):
    """
    Mengirim email dengan template HTML, attachment foto dari direktori, collage foto, dan video.

    Parameters:
    - email_pengirim: email pengirim
    - email_password: password email pengirim
    - penerima: email penerima
    - template: path ke file template HTML
    - subject: subjek email
    - photo_dir: direktori yang berisi foto-foto
    - collage_path: path ke file collage
    - video_path: path ke file video
    """
    # Konfigurasi email
    msg = MIMEMultipart('mixed')
    msg['Subject'] = subject
    msg['From'] = email_pengirim
    msg['To'] = penerima

    # Membaca dan menambahkan template HTML
    with open(template, 'r') as f:
        html = f.read()
    html_part = MIMEText(html, 'html')
    msg.attach(html_part)

    # Menambahkan semua foto (images) di folder photo_dir sebagai attachment.
    # Sebelumnya hanya mengirim photo_*.png; sekarang kirim semua file image agar "semuanya" terkirim.
    allowed_ext = ('.png', '.jpg', '.jpeg', '.webp')
    photo_files = []
    try:
        for name in os.listdir(photo_dir):
            file_path = os.path.join(photo_dir, name)
            if not os.path.isfile(file_path):
                continue
            if name.lower().endswith(allowed_ext):
                photo_files.append(name)
    except FileNotFoundError:
        photo_files = []

    photo_files.sort()

    for photo_file in photo_files:
        file_path = os.path.join(photo_dir, photo_file)
        attach_file(msg, file_path)

    # Menambahkan collage sebagai attachment (cek apakah file ada)
    if collage_path and os.path.exists(collage_path):
        attach_file(msg, collage_path)
    else:
        print(f"Collage file tidak ditemukan di {collage_path}")

    # Menambahkan video sebagai attachment
    if os.path.exists(video_path):
        attach_file(msg, video_path)

    try:
        # Mengirim email
        with smtplib.SMTP_SSL('smtp.gmail.com', 465) as smtp:
            smtp.login(email_pengirim, email_password)
            smtp.send_message(msg)
            time.sleep(0.15)
        print(f"Email berhasil dikirim ke {penerima} dengan {len(photo_files)} foto, collage, dan video")
    except Exception as e:
        print(f"Gagal mengirim email ke {penerima}: {e}")


def main():
    parser = argparse.ArgumentParser(description="Kirim email dengan template HTML, foto, dan video.")
    parser.add_argument('--email_pengirim', type=str, required=True, help="Email pengirim")
    parser.add_argument('--email_password', type=str, required=True, help="Password email pengirim")
    parser.add_argument('--penerima', type=str, required=True, help="Email penerima")
    parser.add_argument('--template', type=str, required=True, help="Path ke file template HTML")
    parser.add_argument('--subject', type=str, required=True, help="Subjek email")
    parser.add_argument('--photo_dir', type=str, required=True, help="Direktori yang berisi foto-foto")
    parser.add_argument('--collage_path', type=str, required=True, help="Path ke file collage foto")  # Perubahan di sini
    parser.add_argument('--video_path', type=str, required=True, help="Path ke file video")

    args = parser.parse_args()

    kirim_email_dengan_media(
        email_pengirim=args.email_pengirim,
        email_password=args.email_password,
        penerima=args.penerima,
        template=args.template,
        subject=args.subject,
        photo_dir=args.photo_dir,
        collage_path=args.collage_path,  # Perubahan di sini
        video_path=args.video_path
    )


if __name__ == "__main__":
    main()
