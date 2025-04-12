-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2024 at 01:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `datatrain`
--

-- --------------------------------------------------------

--
-- Table structure for table `org`
--

CREATE TABLE `org` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `state` varchar(3) NOT NULL,
  `postcode` int(4) NOT NULL,
  `suburb` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `org`
--

INSERT INTO `org` (`id`, `name`, `state`, `postcode`, `suburb`, `type`) VALUES
(1, 'Charles Sturt University', 'NSW', 0, '', 'uni'),
(2, 'Macquarie University', 'NSW', 0, '', 'uni'),
(3, 'Southern Cross University', 'NSW', 0, '', 'uni'),
(4, 'The University of New England', 'NSW', 0, '', 'uni'),
(5, 'The University of Newcastle', 'NSW', 0, '', 'uni'),
(6, 'The University of Sydney', 'NSW', 0, '', 'uni'),
(7, 'University of New South Wales', 'NSW', 0, '', 'uni'),
(8, 'University of Technology Sydney', 'NSW', 0, '', 'uni'),
(9, 'University of Wollongong', 'NSW', 0, '', 'uni'),
(10, 'Western Sydney University', 'NSW', 0, '', 'uni'),
(11, 'Private Universities (Table B)', 'NSW', 0, '', 'uni'),
(12, 'Not applicable', 'NSW', 0, '', 'uni'),
(13, 'Private Universities (Table C)', 'NSW', 0, '', 'uni'),
(14, 'Not applicable', 'NSW', 0, '', 'uni'),
(15, 'Other Approved Higher Education Institutions', 'NSW', 0, '', 'uni'),
(16, 'Academy of Information Technology', 'NSW', 0, '', 'uni'),
(17, 'Alphacrucis College', 'NSW', 0, '', 'uni'),
(18, 'Australasian College of Health and Wellness', 'NSW', 0, '', 'uni'),
(19, 'Australian Academy of Music and Performing Arts', 'NSW', 0, '', 'uni'),
(20, 'Australian College of Applied Psychology', 'NSW', 0, '', 'uni'),
(21, 'Australian Film, Television and Radio School', 'NSW', 0, '', 'uni'),
(22, 'Australian Institute of Management Education &\nTraining', 'NSW', 0, '', 'uni'),
(23, 'Australian Institute of Music', 'NSW', 0, '', 'uni'),
(24, 'Australian National Institute of Management and Commerce', 'NSW', 0, '', 'uni'),
(25, 'Avondale College of Higher Education', 'NSW', 0, '', 'uni'),
(26, 'Campion College', 'NSW', 0, '', 'uni'),
(27, 'Excelsia College', 'NSW', 0, '', 'uni'),
(28, 'Health Education & Training Institute', 'NSW', 0, '', 'uni'),
(29, 'Higher Education Leadership Institute', 'NSW', 0, '', 'uni'),
(30, 'International College of Management, Sydney', 'NSW', 0, '', 'uni'),
(31, 'JMC Academy', 'NSW', 0, '', 'uni'),
(32, 'Kaplan Business School', 'NSW', 0, '', 'uni'),
(33, 'Kaplan Higher Education', 'NSW', 0, '', 'uni'),
(34, 'Kent Institute Australia', 'NSW', 0, '', 'uni'),
(35, 'Kings Own Institute', 'NSW', 0, '', 'uni'),
(36, 'Macleay College', 'NSW', 0, '', 'uni'),
(37, 'Moore Theological College', 'NSW', 0, '', 'uni'),
(38, 'Morling College', 'NSW', 0, '', 'uni'),
(39, 'Nan Tien Institute', 'NSW', 0, '', 'uni'),
(40, 'National Art School', 'NSW', 0, '', 'uni'),
(41, 'S P Jain School of Global Management', 'NSW', 0, '', 'uni'),
(42, 'SAE Creative Media Institute', 'NSW', 0, '', 'uni'),
(43, 'Study Group Australia Pty Ltd', 'NSW', 0, '', 'uni'),
(44, 'Sydney College of Divinity', 'NSW', 0, '', 'uni'),
(45, 'Sydney Institute of Business and Technology', 'NSW', 0, '', 'uni'),
(46, 'Sydney Institute of Traditional Chinese Medicine', 'NSW', 0, '', 'uni'),
(47, 'TAFE NSW', 'NSW', 0, '', 'uni'),
(48, 'Tabor College NSW', 'NSW', 0, '', 'uni'),
(49, 'The Australian College of Physical Education', 'NSW', 0, '', 'uni'),
(50, 'The Australian Institute of Theological Education', 'NSW', 0, '', 'uni'),
(51, 'The College of Law', 'NSW', 0, '', 'uni'),
(52, 'The National Institute of Dramatic Art', 'NSW', 0, '', 'uni'),
(53, 'Think: Colleges Pty Ltd', 'NSW', 0, '', 'uni'),
(54, 'UOW College', 'NSW', 0, '', 'uni'),
(55, 'UTS:INSEARCH', 'NSW', 0, '', 'uni'),
(56, 'Universal Business School Sydney (UBSS)', 'NSW', 0, '', 'uni'),
(57, 'Wentworth Institute', 'NSW', 0, '', 'uni'),
(58, 'Whitehouse Institute of Design; Australia', 'NSW', 0, '', 'uni'),
(59, 'Deakin University', 'VIC', 0, '', 'uni'),
(60, 'Federation University Australia', 'VIC', 0, '', 'uni'),
(61, 'La Trobe University', 'VIC', 0, '', 'uni'),
(62, 'Monash University', 'VIC', 0, '', 'uni'),
(63, 'RMIT University', 'VIC', 0, '', 'uni'),
(64, 'Swinburne University of Technology', 'VIC', 0, '', 'uni'),
(65, 'The University of Melbourne', 'VIC', 0, '', 'uni'),
(66, 'Victoria University', 'VIC', 0, '', 'uni'),
(67, 'Private Universities (Table B)', 'VIC', 0, '', 'uni'),
(68, 'University of Divinity', 'VIC', 0, '', 'uni'),
(69, 'Private Universities (Table C)', 'VIC', 0, '', 'uni'),
(70, 'Not applicable', 'VIC', 0, '', 'uni'),
(71, 'Other Approved Higher Education Institutions', 'VIC', 0, '', 'uni'),
(72, 'Australian Guild of Music Education Inc.', 'VIC', 0, '', 'uni'),
(73, 'Box Hill Institute', 'VIC', 0, '', 'uni'),
(74, 'Chisholm Institute', 'VIC', 0, '', 'uni'),
(75, 'Collarts', 'VIC', 0, '', 'uni'),
(76, 'Deakin College', 'VIC', 0, '', 'uni'),
(77, 'Eastern College Australia', 'VIC', 0, '', 'uni'),
(78, 'Holmes Institute', 'VIC', 0, '', 'uni'),
(79, 'Holmesglen Institute of TAFE', 'VIC', 0, '', 'uni'),
(80, 'ISN Psychology Pty Ltd', 'VIC', 0, '', 'uni'),
(81, 'LCI Melbourne', 'VIC', 0, '', 'uni'),
(82, 'La Trobe Melbourne', 'VIC', 0, '', 'uni'),
(83, 'Leo Cussen Institute', 'VIC', 0, '', 'uni'),
(84, 'MIECAT', 'VIC', 0, '', 'uni'),
(85, 'Marcus Oldham College', 'VIC', 0, '', 'uni'),
(86, 'Melbourne Institute of Technology', 'VIC', 0, '', 'uni'),
(87, 'Monash College', 'VIC', 0, '', 'uni'),
(88, 'National Institute of Organisation Dynamics Aust', 'VIC', 0, '', 'uni'),
(89, 'Northern Melbourne Institute of TAFE', 'VIC', 0, '', 'uni'),
(90, 'Photography Studies College (Melbourne)', 'VIC', 0, '', 'uni'),
(91, 'Stotts Colleges', 'VIC', 0, '', 'uni'),
(92, 'The Cairnmillar Institute', 'VIC', 0, '', 'uni'),
(93, 'VIT (Victorian Institute of Technology)', 'VIC', 0, '', 'uni'),
(94, 'William Angliss Institute of TAFE', 'VIC', 0, '', 'uni'),
(95, 'CQUniversity', 'QLD', 0, '', 'uni'),
(96, 'Griffith University', 'QLD', 0, '', 'uni'),
(97, 'James Cook University', 'QLD', 0, '', 'uni'),
(98, 'Queensland University of Technology', 'QLD', 0, '', 'uni'),
(99, 'The University of Queensland', 'QLD', 0, '', 'uni'),
(100, 'University of Southern Queensland', 'QLD', 0, '', 'uni'),
(101, 'University of the Sunshine Coast', 'QLD', 0, '', 'uni'),
(102, 'Bond University', 'QLD', 0, '', 'uni'),
(103, 'Australian Institute of Professional Counsellors', 'QLD', 0, '', 'uni'),
(104, 'Christian Heritage College', 'QLD', 0, '', 'uni'),
(105, 'Endeavour College of Natural Health', 'QLD', 0, '', 'uni'),
(106, 'Gestalt Therapy Brisbane', 'QLD', 0, '', 'uni'),
(107, 'Griffith College', 'QLD', 0, '', 'uni'),
(108, 'Jazz Music Institute', 'QLD', 0, '', 'uni'),
(109, 'TAFE Queensland', 'QLD', 0, '', 'uni'),
(110, 'The Performing Arts Conservatory', 'QLD', 0, '', 'uni'),
(111, 'Curtin University', 'WA', 0, '', 'uni'),
(112, 'Edith Cowan University', 'WA', 0, '', 'uni'),
(113, 'Murdoch University', 'WA', 0, '', 'uni'),
(114, 'The University of Western Australia', 'WA', 0, '', 'uni'),
(115, 'The University of Notre Dame Australia', 'WA', 0, '', 'uni'),
(116, 'Other Approved Higher Education Institutions', 'WA', 0, '', 'uni'),
(117, 'Curtin College', 'WA', 0, '', 'uni'),
(118, 'Edith Cowan College', 'WA', 0, '', 'uni'),
(119, 'Engineering Institute of Technology Pty Ltd', 'WA', 0, '', 'uni'),
(120, 'North Metropolitan TAFE', 'WA', 0, '', 'uni'),
(121, 'Perth Bible College', 'WA', 0, '', 'uni'),
(122, 'South Metropolitan TAFE', 'WA', 0, '', 'uni'),
(123, 'Flinders University', 'SA', 0, '', 'uni'),
(124, 'The University of Adelaide', 'SA', 0, '', 'uni'),
(125, 'University of South Australia', 'SA', 0, '', 'uni'),
(126, 'Torrens University Australia', 'SA', 0, '', 'uni'),
(127, 'Carnegie Mellon University Australia', 'SA', 0, '', 'uni'),
(128, 'Other Approved Higher Education Institutions', 'SA', 0, '', 'uni'),
(129, 'Adelaide Central School of Art', 'SA', 0, '', 'uni'),
(130, 'Adelaide College of Divinity', 'SA', 0, '', 'uni'),
(131, 'Australian Institute of Business', 'SA', 0, '', 'uni'),
(132, 'Eynesbury', 'SA', 0, '', 'uni'),
(133, 'Ikon Institute of Australia', 'SA', 0, '', 'uni'),
(134, 'International College of Hotel Management', 'SA', 0, '', 'uni'),
(135, 'Le Cordon Bleu Australia', 'SA', 0, '', 'uni'),
(136, 'South Aust Institute of Business & Technology', 'SA', 0, '', 'uni'),
(137, 'TAFE SA', 'SA', 0, '', 'uni'),
(138, 'Tabor Adelaide', 'SA', 0, '', 'uni'),
(139, 'University of Tasmania', 'TAS', 0, '', 'uni'),
(140, 'Batchelor Institute of Indigenous Tertiary Education', 'NT', 0, '', 'uni'),
(141, 'Charles Darwin University', 'NT', 0, '', 'uni'),
(142, 'The Australian National University', 'ACT', 0, '', 'uni'),
(143, 'University of Canberra', 'ACT', 0, '', 'uni'),
(144, 'Australian College of Theology', 'ACT', 0, '', 'uni'),
(145, 'Australian Catholic University', 'ACT', 0, '', 'uni'),
(146, 'Australia College of Nursing Ltd', 'ACT', 0, '', 'uni'),
(147, 'Canberra Institute of Technology', 'ACT', 0, '', 'uni');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `org`
--
ALTER TABLE `org`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `org`
--
ALTER TABLE `org`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
