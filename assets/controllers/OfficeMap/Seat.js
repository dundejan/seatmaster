import React, {useState} from 'react';
import { useDrag } from 'react-dnd';
import PropTypes from "prop-types";
import { Tooltip as ReactTooltip } from "react-tooltip";
import {ChairIcon} from "./ChairIcon/ChairIcon";

export function Seat({ id, left, top, currentAssignments }) {
	const [isDragging, setIsDragging] = useState(false);
	const [, ref] = useDrag({
		type: 'SEAT',
		item: { id },
		end: () => setIsDragging(false),
	});

	const handleMouseDown = () => setIsDragging(true);
	const handleMouseUp = () => setIsDragging(false);

	let title, color;

	if (typeof currentAssignments !== "undefined" && currentAssignments.length === 1) {
		let from, to, name;
		color = '#ff0000';

		name = currentAssignments[0].person.firstName + ' ' + currentAssignments[0].person.lastName;

		// One time assignment
		if (typeof currentAssignments[0].dayOfWeek === 'undefined') {
			from = new Date(currentAssignments[0].fromDate);
			to = new Date(currentAssignments[0].toDate);
			title = (
				<div>
					This chair is currently occupied.<br />
					PERSON: {name}<br />
					FROM: {from.getHours()}:{from.getMinutes().toString().padStart(2, '0')}<br />
					TO: {to.getHours()}:{to.getMinutes().toString().padStart(2, '0')}
				</div>
			);
		}
		// Repeated assignment
		else {
			from = new Date(currentAssignments[0].fromTime);
			to = new Date(currentAssignments[0].toTime);
			title = (
				<div>
					This chair is currently occupied.<br />
					PERSON: {name}<br />
					FROM: {from.getUTCHours()}:{from.getUTCMinutes().toString().padStart(2, '0')}<br />
					TO: {to.getUTCHours()}:{to.getUTCMinutes().toString().padStart(2, '0')}
				</div>
			);
		}
	}
	else {
		color = '#00ff00';
		title = `This chair is currently free.`;
	}

	return (
		<div
			data-tooltip-id={id}
			ref={ref}
			style={{ left, top, position: 'absolute' }}
			onMouseDown={handleMouseDown}
			onMouseUp={handleMouseUp}
		>
			<ChairIcon color={color} size="50px" />
			<p>{`Seat ${id}`}</p>
			{!isDragging && (
				<ReactTooltip id={id} place="top">
					{title}
				</ReactTooltip>
			)}
		</div>
	);
}

Seat.propTypes = {
	id: PropTypes.number,
	left: PropTypes.number,
	top: PropTypes.number,
	currentAssignments: PropTypes.array,
}