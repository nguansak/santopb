import serial

#ser = serial.Serial("/dev/ttyACM0", 9600)
while True :
    # Check whether the user has typed anything (timeout of .2 sec):
    inp, outp, err = select.select([sys.stdin, self.ser], [], [], .2)

    # If the user has typed anything, send it to the Arduino:
    if sys.stdin in inp :
        line = sys.stdin.readline()
        self.ser.write(line)

    # If the Arduino has printed anything, display it:
    if self.ser in inp :
	line = self.ser.readline().strip()
	print "Arduino:", line

