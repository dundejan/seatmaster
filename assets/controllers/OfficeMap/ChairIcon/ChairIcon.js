import React from 'react';
import {ChairIconSVG} from "./ChairIconSVG";
import PropTypes from "prop-types";

export function ChairIcon({ color, size, rotation }) {
	const style = { transform: `rotate(${rotation}deg)` };
	return (
		<div style={style}>
			<ChairIconSVG fill={color} size={size} />
		</div>
	);
}

ChairIcon.propTypes = {
	color: PropTypes.string,
	size: PropTypes.string,
	rotation: PropTypes.number,
}