import React from 'react';
import { useDrag } from 'react-dnd';
import PropTypes from "prop-types";

function ChairIconSVG({ fill, size = "800px" }) {
	return (
		<svg width={size} height={size} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
			<path d="M7.40192 4.5C7 5.19615 7 6.13077 7 8V11.0269C7.43028 10.9999 7.91397 11 8.43477 11H15.5648C16.0858 11 16.5696 10.9999 17 11.0269V8C17 6.13077 17 5.19615 16.5981 4.5C16.3348 4.04394 15.9561 3.66523 15.5 3.40192C14.8038 3 13.8692 3 12 3C10.1308 3 9.19615 3 8.5 3.40192C8.04394 3.66523 7.66523 4.04394 7.40192 4.5Z" fill={fill} />
			<path d="M6.25 15.9914C5.74796 15.9711 5.44406 15.9032 5.236 15.6762C4.93926 15.3523 4.97792 14.9018 5.05525 14.0008C5.11107 13.3503 5.2373 12.9125 5.52274 12.5858C6.0345 12 6.85816 12 8.50549 12H15.4945C17.1418 12 17.9655 12 18.4773 12.5858C18.7627 12.9125 18.8889 13.3503 18.9448 14.0008C19.0221 14.9018 19.0607 15.3523 18.764 15.6762C18.5559 15.9032 18.252 15.9711 17.75 15.9914V20.9999C17.75 21.4142 17.4142 21.7499 17 21.7499C16.5858 21.7499 16.25 21.4142 16.25 20.9999V16H7.75V20.9999C7.75 21.4142 7.41421 21.7499 7 21.7499C6.58579 21.7499 6.25 21.4142 6.25 20.9999V15.9914Z" fill={fill} />
		</svg>
	);
}

function ChairIcon({ color, size }) {
	return (
		<ChairIconSVG fill={color} size={size} />
	);
}

export default function Seat({ id, left, top, occupied }) {
	const [, ref] = useDrag({
		type: 'SEAT',
		item: { id }
	});

	if (occupied) {
		const from = new Date(occupied.fromDate);
		const to = new Date(occupied.toDate);
		const title = `This chair is currently occupied.\nPERSON: TODO\nFROM: ${from}\nTO: ${to}`;

		return (
			<div title={title} ref={ref} style={{ left, top, position: 'absolute' }}>
				<ChairIcon color={"#FF0000"} size="50px" />
				<p>{`Seat ${id}`}</p>
			</div>
		);
	}
	else {
		const title = `This chair is currently free.`;

		return (
			<div title={title} ref={ref} style={{left, top, position: 'absolute'}}>
				<ChairIcon color={"#00ff00"} size="50px"/>
				<p>{`Seat ${id}`}</p>
			</div>
		);
	}
}

Seat.propTypes = {
	id: PropTypes.number,
	left: PropTypes.number,
	top: PropTypes.number,
	occupied: PropTypes.object,
}

ChairIconSVG.propTypes = {
	fill: PropTypes.string,
	size: PropTypes.string,
}

ChairIcon.propTypes = {
	color: PropTypes.string,
	size: PropTypes.string,
}