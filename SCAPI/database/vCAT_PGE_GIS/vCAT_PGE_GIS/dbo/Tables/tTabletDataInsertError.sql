CREATE TABLE [dbo].[tTabletDataInsertError] (
    [tTabletDataInsertErrorID] INT                IDENTITY (1, 1) NOT NULL,
    [SvrDTLT]                  DATETIME           CONSTRAINT [DFtTabletDataInsertErrorSvrDTLT] DEFAULT (getdate()) NULL,
    [SvrDTLT_Offset]           DATETIMEOFFSET (7) CONSTRAINT [DFtTabletDataInsertErrorSvrDTLT_Offset] DEFAULT (sysdatetimeoffset()) NULL,
    [InsertedData]             VARCHAR (MAX)      NULL,
    [Comments]                 VARCHAR (200)      NULL,
    [ErrorNumber]              INT                NULL,
    [ErrorMessage]             VARCHAR (500)      NULL,
    [Reprocessed]              BIT                CONSTRAINT [DFtTabletDataInsertErrorReprocessed] DEFAULT ((0)) NULL
);

