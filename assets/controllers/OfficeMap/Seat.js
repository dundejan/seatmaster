import React, {useState} from 'react';
import { useDrag } from 'react-dnd';
import PropTypes from "prop-types";
import { Tooltip as ReactTooltip } from "react-tooltip";
import {ChairIcon} from "./ChairIcon/ChairIcon";
import {Typography} from "@mui/material";

export function Seat({ id, left, top, rotation, currentAssignments, setSeatInfo, changeChairRotation }) {
	const [isDragging, setIsDragging] = useState(false);
	const [, ref] = useDrag({
		type: 'SEAT',
		item: { id },
		end: () => setIsDragging(false),
	});

	const handleMouseDown = () => setIsDragging(true);
	const handleMouseUp = () => setIsDragging(false);
	const handleClick = () => setSeatInfo({ id: id, info: info });
	const handleDoubleClick = (id) => changeChairRotation(id);

	let tooltip, color, personId, info;

	if (typeof currentAssignments !== "undefined" && currentAssignments.length === 1) {
		let from, to, name;
		color = '#ff0000';

		name = currentAssignments[0].person.firstName + ' ' + currentAssignments[0].person.lastName;
		personId = currentAssignments[0].person.id;

		// One time assignment
		if (typeof currentAssignments[0].dayOfWeek === 'undefined') {
			from = new Date(currentAssignments[0].fromDate);
			to = new Date(currentAssignments[0].toDate);
			tooltip = (
				<div>
					<b>One time assignment</b><br />
					PERSON: {name}<br />
					FROM: {from.getHours()}:{from.getMinutes().toString().padStart(2, '0')}<br />
					TO: {to.getHours()}:{to.getMinutes().toString().padStart(2, '0')}
				</div>
			);
			info = (
				<div>
					<b>One time assignment</b><br />
					PERSON: <a href={`/person/${personId}`}>{name}</a><br />
					FROM: {from.getHours()}:{from.getMinutes().toString().padStart(2, '0')}<br />
					TO: {to.getHours()}:{to.getMinutes().toString().padStart(2, '0')}
				</div>
			);
		}
		// Repeated assignment
		else {
			from = new Date(currentAssignments[0].fromTime);
			to = new Date(currentAssignments[0].toTime);
			tooltip = (
				<div>
					<b>Repeated assignment</b><br />
					PERSON: {name}<br />
					FROM: {from.getUTCHours()}:{from.getUTCMinutes().toString().padStart(2, '0')}<br />
					TO: {to.getUTCHours()}:{to.getUTCMinutes().toString().padStart(2, '0')}
				</div>
			);
			info = (
				<div>
					<b>Repeated assignment</b><br />
					PERSON: <a href={`/person/${personId}`}>{name}</a><br />
					FROM: {from.getUTCHours()}:{from.getUTCMinutes().toString().padStart(2, '0')}<br />
					TO: {to.getUTCHours()}:{to.getUTCMinutes().toString().padStart(2, '0')}
				</div>
			);
		}
	}
	else {
		color = '#00ff00';
		tooltip = `This chair is currently free.`;
		info = `This chair is currently free.`;
	}

	return (
		<div
			data-tooltip-id={id}
			ref={ref}
			style={{ left, top, position: 'absolute' }}
			onMouseDown={handleMouseDown}
			onMouseUp={handleMouseUp}
			onClick={handleClick}
			onDoubleClick={() => handleDoubleClick(id)}
		>
			<div style={{ position: 'relative', textAlign: 'center' }}>
				<div
					style={{
						transform: `rotate(${rotation}deg)`,
						margin: '0 auto', // Center the chair if necessary
					}}
				>
					<ChairIcon color={color} size="40" />
				</div>
				<Typography
					variant="body2"
					style={{
						fontSize: '12px',
						marginTop: '4px', // Adjust the space between the icon and text as needed
						display: 'block', // Ensure the text is on its own line
					}}
				>
					{`SEAT ${id}`}
				</Typography>
			</div>
			{!isDragging && (
				<ReactTooltip id={id} place="top" style={{ zIndex: 1000 }}>
					{tooltip}
				</ReactTooltip>
			)}
		</div>
	);
}

Seat.propTypes = {
	id: PropTypes.number,
	left: PropTypes.number,
	top: PropTypes.number,
	rotation: PropTypes.number,
	currentAssignments: PropTypes.array,
	setSeatInfo: PropTypes.func,
	changeChairRotation: PropTypes.func,
}