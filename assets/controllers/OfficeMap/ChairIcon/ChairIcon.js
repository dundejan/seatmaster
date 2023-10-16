import React from 'react';
import {ChairIconSVG} from "./ChairIconSVG";
import PropTypes from "prop-types";

export function ChairIcon({ color, size }) {
	return (
		<ChairIconSVG fill={color} size={size} />
	);
}

ChairIcon.propTypes = {
	color: PropTypes.string,
	size: PropTypes.string,
}