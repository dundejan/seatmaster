import React from 'react';
import { useDrag } from 'react-dnd';
import chairImage from '../../images/chair.jpg';
import PropTypes from "prop-types";

export default function Seat({ id, left, top }) {
	const [, ref] = useDrag({
		type: 'SEAT',
		item: { id }
	});

	return (
		<div ref={ref} style={{ left, top, position: 'absolute' }}>
			<img src={chairImage} alt={`Seat ${id}`} style={{ width: '50px', height: '50px' }} />
			<p>{`Seat ${id}`}</p>
		</div>
	);
}

Seat.propTypes = {
	id: PropTypes.number,
	left: PropTypes.number,
	top: PropTypes.number,
}