import React from "react";
import { render } from "react-dom";
import OfficeMapApp from './OfficeMap/OfficeMapApp'

const targetElement = document.getElementById('office-map');
const officeId = targetElement.getAttribute('data-office-id');

render(
	<OfficeMapApp officeId={officeId} />,
	document.getElementById('office-map')
)