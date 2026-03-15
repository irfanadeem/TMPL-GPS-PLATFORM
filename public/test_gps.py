import socket

# Configuration
IP = '0.0.0.0'
PORT = 5023

with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
    s.bind((IP, PORT))
    s.listen(1)
    print(f"Listening on {PORT}...")
    
    conn, addr = s.accept()
    with conn:
        print(f"Connected by {addr}")
        while True:
            data = conn.recv(1024)
            if not data:
                break
            
            # Print the incoming Hex data
            print(f"Received: {data.hex().upper()}")

            # Check if it's a Login Packet (Starts with 7878 and Protocol 01)
            if len(data) > 4 and data[0:2] == b'\x78\x78' and data[3:4] == b'\x01':
                # Extract Serial Number (Bytes 13-14)
                serial = data[-6:-4] 
                # Construct ACK: Start(7878) Length(05) Type(01) Serial(XXXX) ErrorCheck(XXXX) End(0D0A)
                # For testing, a simple static response often works to keep the socket open:
                # This is a generic ACK for protocol 01
                response = b'\x78\x78\x05\x01' + serial + b'\x0D\x0A'
                conn.sendall(response)
                print(f"Sent ACK: {response.hex().upper()}")