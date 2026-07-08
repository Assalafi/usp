// setup timer with set textual date in the future
const timer1 = new CountdownTimer({
	selector: "#clock1",
	targetDate: new Date("December, 14 2023 23:59:00"),
});

timer1.startTimer();
